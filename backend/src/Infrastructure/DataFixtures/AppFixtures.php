<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures;

use App\Domain\Entity\Column;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $users = $this->loadUsers($manager);
        $columns = $this->loadColumns($manager);
        $statuses = $this->loadStatuses($manager);
        $manager->flush();

        $this->loadTasks($manager, $users, $columns, $statuses);
        $manager->flush();
    }

    /** @return User[] */
    private function loadUsers(ObjectManager $manager): array
    {
        $usersData = [
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'isAdmin' => true, 'password' => 'admin', 'avatar' => '/public/admin.jpg'],
            ['name' => 'Vasily Lozhkin', 'email' => 'vasia@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user5.jpg'],
            ['name' => 'Sergey Yesenin', 'email' => 'seresen@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user2.jpg'],
            ['name' => 'Alexey Yashin', 'email' => 'yashin@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user3.jpg'],
            ['name' => 'Igor Pyatin', 'email' => 'five@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user6.jpg'],
            ['name' => 'Elena Shapovalova', 'email' => 'six@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user1.jpg'],
            ['name' => 'Andrey Ilyin', 'email' => 'seven@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user7.jpg'],
            ['name' => 'Diana Averina', 'email' => 'eight@example.com', 'isAdmin' => false, 'password' => '123456', 'avatar' => '/public/user4.jpg'],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setIsAdmin($data['isAdmin']);
            $user->setAvatar($data['avatar']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $manager->persist($user);
            $users[] = $user;
        }
        return $users;
    }

    /** @return Column[] */
    private function loadColumns(ObjectManager $manager): array
    {
        $titles = ['Scheduled', 'In work', 'Under review', 'Done', 'For removal'];
        $columns = [];
        foreach ($titles as $title) {
            $column = new Column();
            $column->setTitle($title);
            $manager->persist($column);
            $columns[] = $column;
        }
        return $columns;
    }

    /** @return Status[] */
    private function loadStatuses(ObjectManager $manager): array
    {
        $names = ['Active', 'In progress', 'Completed'];
        $statuses = [];
        foreach ($names as $name) {
            $status = new Status();
            $status->setName($name);
            $manager->persist($status);
            $statuses[] = $status;
        }
        return $statuses;
    }

    /** @param User[] $users */
    /** @param Column[] $columns */
    /** @param Status[] $statuses */
    private function loadTasks(ObjectManager $manager, array $users, array $columns, array $statuses): void
    {
        $possibleTags = ['Urgently', 'Design', 'HTML', 'Frontend', 'Backend', 'Does not burn'];
        $total = 20;

        $sortOrderByColumn = [];

        for ($i = 0; $i < $total; $i++) {
            $task = new Task();
            $task->setTitle('Task ' . ($i + 1));
            $task->setDescription('Task description ' . ($i + 1));

            $columnIdx = $i % 4 ? ($i % 4) - 1 : -1;
            $column = $columnIdx >= 0 && $columnIdx < count($columns) ? $columns[$columnIdx] : null;
            $task->setColumn($column);

            $columnKey = $column ? $column->getTitle() : 'null';
            $sortOrderByColumn[$columnKey] = ($sortOrderByColumn[$columnKey] ?? -1) + 1;
            $task->setSortOrder($sortOrderByColumn[$columnKey]);

            $statusIdx = $i % 3 ? ($i % 3) - 1 : -1;
            $task->setStatus($statusIdx >= 0 && $statusIdx < count($statuses) ? $statuses[$statusIdx] : null);

            $userIdx = $i % 8 ? $i % 8 : -1;
            $task->setUser($userIdx >= 0 && $userIdx < count($users) ? $users[$userIdx] : null);

            $task->setDueDate($i > 16 ? new \DateTime() : null);

            $numTags = random_int(0, 4);
            $tags = [];
            for ($j = 0; $j < $numTags; $j++) {
                $tags[] = $possibleTags[random_int(0, 4)];
            }
            $task->setTags(implode('#', $tags));

            $manager->persist($task);
        }
    }
}
