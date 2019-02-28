<?php

namespace App\Arrobe;

use Symfony\Component\Console\Output\OutputInterface;

class NiceCommandOutput
{

    public function writeBlockColor(OutputInterface $output, string $text, string $color): self
    {
        $text = ' '.$text.' ';
        $spaces = '';
        $spacesCount = strlen($text);
        for ($i=0; $i < $spacesCount; $i++) {
            $spaces .= ' ';
        }
        $this->writeLnColor($output, '', $color);
        $this->writeLnColor($output, $spaces, $color);
        $this->writeLnColor($output, $text, $color);
        $this->writeLnColor($output, $spaces, $color);

        return $this;
    }

    public function writeLnColor(OutputInterface $output, string $text='', string $color=''): self
    {
        $colorCode = '';
        $endColorCode = '';
        switch ($color) {
            case 'success':
                $colorCode = '<fg=black;bg=green>';
                $endColorCode = '</>';
                break;
            case 'info':
                $colorCode = '<fg=black;bg=cyan>';
                $endColorCode = '</>';
                break;
            case 'warning':
                $colorCode = '<fg=black;bg=yellow>';
                $endColorCode = '</>';
                break;
            case 'error':
                $colorCode = '<fg=black;bg=red>';
                $endColorCode = '</>';
                break;
        }
        $output->writeln($colorCode.$text.$endColorCode);
        return $this;
    }

}

?>
