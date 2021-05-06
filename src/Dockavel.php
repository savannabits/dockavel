<?php

namespace Savannabits\Dockavel;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class Dockavel extends Command
{
    protected $name='docker:install';
    protected $description="Run an install to publish all the resources needed to run docker";

    public function handle() {
        $force = $this->hasOption('force');
        $php = $this->choice("Choose your version of PHP",["php8.x","php7.4"], ["php7.4"]);
        if (!$php) {
            $this->error("You must specify a php version");
            die();
        }
        $image = $this->argument('image');
        $network = $this->argument('network') ?? "bridge";
        $this->prepareEnv();
        $this->publishConfig($php,$force);
        if ($network!=='bridge' && $network !=='docker' && $this->confirm("would you like to create the $network bridge network?")) {
            $this->cmd("docker network create $network");
        }
        if ($this->confirm("would you like to create the ${$image}db docker volume for the database?")) {
            $this->cmd("docker volume create ${image}db");
        }
        if ($this->confirm("My Work here is done. Do you want to uninstall me now?")) {
            $this->cleanUp();
        }
    }
    public function cmd($cmd) {
        $process = Process::fromShellCommandline($cmd,null,array_merge($_SERVER, $_ENV),null,null);
        $process->run(function ($type,$line){
            $this->line($line);
        });
    }
    protected function replaceInFile($filename, $search, $replace) {
        $content=file_get_contents($filename);
        str_replace($search,$replace,$content);
        file_put_contents($filename, $content);
    }
    protected function prepareEnv() {
        $image = $this->argument('image');
        $network = $this->argument('network');
        $env = __DIR__."/../env/.env.docker";
        $this->replaceInFile($env,":image:",$image);
        $this->replaceInFile($env,":network:",$network);
        //Then publish it
        $this->cmd("cp ".$env." ".base_path(".env.docker"));
    }
    protected function publishConfig(string $phpVersion, bool $force=false) {
        $this->info('Publishing docker Config...');
        $this->call('vendor:publish', [
            '--provider' => 'Savannabits\Dockavel\DockavelServiceProvider',
            '--force' =>$force
        ]);

        //Configure the right version
        $this->cmd("mv ".base_path("docker/$phpVersion")." ".base_path(".")." -R");
        $this->cmd("rm -rf docker");
    }

    private function cleanUp(): void
    {
        $this->info('Cleaning up and removing Arc...');
        $this->cmd('composer remove savannabits/dockavel --ignore-platform-reqs');
    }

    protected function getOptions(): array
    {
        return [
            ["force","f",InputOption::VALUE_NONE, "If the docker config already exists, force overwrites the existing files when publishing."]
        ];
    }
    protected function getArguments(): array
    {
        return [
            ['image', InputArgument::REQUIRED, "The name of the image with which the docker containers will be prefixed."],
            ['network', InputArgument::OPTIONAL, "The name of the docker bridge network to use (will be created if it doesn't exist)",'bridge']
        ];
    }

}
