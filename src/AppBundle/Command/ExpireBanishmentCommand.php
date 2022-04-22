<?php


namespace AppBundle\Command;


use AppBundle\Entity\Banishment;
use AppBundle\Entity\Course;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ExpireBanishmentCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName("app:expire_banishment")
            ->setDescription("Komenda do wygaszania banów.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $banishments = $this->entityManager->getRepository(Banishment::class)->findActiveExpired();

        $output->writeln(sprintf("Znaleziono <info>%d</info> banów do wygaszenia", count($banishments)));

        foreach ($banishments as $banishment) {
            $this->entityManager->remove($banishment);
        }

        $this->entityManager->flush();
        if(count($banishments) > 0)
            $output->writeln("Udało się usunąć ". count($banishments) ." banów!");
        else
            $output->writeln("Brak banów do usunięcia.");
    }
}