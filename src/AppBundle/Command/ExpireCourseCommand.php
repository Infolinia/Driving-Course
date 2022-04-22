<?php


namespace AppBundle\Command;


use AppBundle\Entity\Course;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ExpireCourseCommand extends Command
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
            ->setName("app:expire_course")
            ->setDescription("Komenda do wygaszania kursu.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $acourses = $this->entityManager->getRepository(Course::class)->findActiveExpired();
        $output->writeln(sprintf("Znaleziono <info>%d</info> kursów do wygaszenia", count($acourses)));

        foreach ($acourses as $acourse) {
            $acourse->setEnabled(Course::DISABLED);
            $this->entityManager->persist($acourse);
        }

        $this->entityManager->flush();
        if(count($acourses) > 0)
            $output->writeln("Udało się zakończyć ". count($acourses) ." kursy!");
        else
            $output->writeln("Brak kursów do zakończenia.");
    }
}