<?php

namespace App\Command;

use App\Entity\Alumni;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-alumni-users',
    description: 'Creates Alumni records for Users with ROLE_ALUMNI that don\'t have one yet',
)]
class SyncAlumniUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Find all Alumni users without an Alumni record
        $orphanedUsers = $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.alumni', 'a')
            ->where('u.roles LIKE :alumni_role')
            ->andWhere('a.id IS NULL')
            ->setParameter('alumni_role', '%' . User::ROLE_ALUMNI . '%')
            ->getQuery()
            ->getResult();

        if (empty($orphanedUsers)) {
            $io->success('No orphaned alumni users found. All alumni have Alumni records!');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d orphaned alumni user(s). Creating Alumni records...', count($orphanedUsers)));

        $created = 0;
        foreach ($orphanedUsers as $user) {
            try {
                $alumni = new Alumni();
                $alumni->setUser($user);
                $alumni->setFirstName($user->getFirstName());
                $alumni->setLastName($user->getLastName());
                $alumni->setEmailAddress($user->getEmail());
                // Use email prefix as student number (can be updated manually later)
                $alumniEmail = $user->getEmail();
                $studentNumber = explode('@', $alumniEmail)[0] ?? $alumniEmail;
                $alumni->setStudentNumber($studentNumber);

                $user->setAlumni($alumni);
                $this->entityManager->persist($alumni);

                $io->writeln(sprintf('  ✓ Created Alumni record for: %s (%s)', $user->getFullName(), $user->getEmail()));
                $created++;
            } catch (\Exception $e) {
                $io->error(sprintf('  ✗ Failed to create Alumni for %s: %s', $user->getEmail(), $e->getMessage()));
            }
        }

        $this->entityManager->flush();
        $io->success(sprintf('Successfully created %d Alumni record(s)!', $created));

        return Command::SUCCESS;
    }
}
