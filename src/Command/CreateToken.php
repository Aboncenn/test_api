<?php

namespace App\Command;

ini_set('memory_limit', -1);

use App\Arrobe\NiceCommandOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Ramsey\Uuid\Uuid;

class CreateToken extends Command
{

    private $em;
    private $o;

    public function __construct(EntityManagerInterface $em, NiceCommandOutput $o){
        parent::__construct();
        $this->em = $em;
        $this->o = $o;
    }

    protected function configure()
    {
        $this
            ->setName('app:create:token')
            ->setDescription('Génère un token (clé d\'API)')
            ->setHelp('Génère un token (clé d\'API)')
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->o->writeBlockColor($output, 'Arrobe - Create - Token', 'info');

        $token = hash('sha256', Uuid::uuid4().Uuid::uuid4());

        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setApikey($token);
        $this->em->persist($user);
        $this->em->flush();

        $this->o->writeBlockColor($output, 'API Key="'.$token.'"', 'warning');

        $this->o->writeBlockColor($output, 'OK ! ', 'success');
    }
}
