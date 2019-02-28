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

class EnableCorsUser extends Command
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
            ->setName('app:create:cors-user')
            ->setDescription('Génère un user CORS pour gérer le preflight')
            ->setHelp('Génère un user CORS pour gérer le preflight')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->o->writeBlockColor($output, 'Enable - CORS - User', 'info');

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'CORS', 'apikey' => 'CORS']);
        if (is_null($user)){
            $user = new User();
            $user->setEmail('CORS');
            $user->setApikey('CORS');
            $this->em->persist($user);
            $this->em->flush();
        }

        $this->o->writeBlockColor($output, 'OK ! ', 'success');
    }
}
