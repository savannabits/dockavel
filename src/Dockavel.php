<?php

namespace Savannabits\Dockavel;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class Dockavel extends Command
{
    protected $signature = 'docker:install 
                         {image : The image name}
                         {network? : The bridge network to use}
                         {--f|force : Whether to overwrite published files}
                        ';
    protected $description = "Run an install to publish all the resources needed to run docker";

    public function handle()
    {
        $this->info("We are here");
        $force = $this->hasOption('force');
        $php = $this->choice("Choose your version of PHP", ["php8.1", "php8.x", "php7.4", "octane"], "php8.1");
        if (!$php) {
            $this->error("You must specify a php version");
            die();
        }
        $image = $this->argument('image');
        $network = $this->argument('network') ?? $image . "net";
        $this->prepareEnv();
        $this->publishConfig($php, $force);
        if ($network !== 'bridge' && $network !== 'docker' && $this->confirm("would you like to create the $network bridge network?")) {
            $this->cmd("docker network create $network");
        }
        if ($this->confirm("would you like to create the {$image}db docker volume for the database?")) {
            $this->cmd("docker volume create {$image}db");
        }
        if ($this->confirm("My Work here is done. Do you want to uninstall me now?")) {
            $this->cleanUp();
        }
    }
    private function cmd($cmd)
    {
        $process = Process::fromShellCommandline($cmd, null, array_merge($_SERVER, $_ENV), null, null);
        $process->run(function ($type, $line) {
            $this->line($line);
        });
    }
    protected function replaceInFile($filename, $search, $replace)
    {
        $content = file_get_contents($filename);
        $newContent = str_replace($search, $replace, $content);
        file_put_contents($filename, $newContent);
    }
    protected function prepareEnv()
    {
        $image = $this->argument('image');
        $network = $this->argument('network');
        $env = __DIR__ . "/../env/.env.docker";
        $dest = base_path(".env.docker");
        $this->cmd("cp -rf $env $dest");
        $this->replaceInFile($dest, ":image:", $image);
        $this->replaceInFile($dest, ":network:", $network);
    }
    protected function publishConfig(string $phpVersion, bool $force = false)
    {
        $this->info('Publishing docker Config...');
        $this->call('vendor:publish', [
            '--provider' => 'Savannabits\Dockavel\DockavelServiceProvider',
            '--force' => $force
        ]);

        //Configure the right version
        $this->cmd("cp -rf " . base_path("docker/$phpVersion/docker-compose.yml") . " " . base_path("."));
        $this->cmd("cp -rf " . base_path("docker/$phpVersion/.docker") . " " . base_path("."));
        $this->cmd("rm -rf docker");
    }

    private function cleanUp(): void
    {
        $this->info('Cleaning up and removing Dockavel...');
        $this->cmd('composer remove savannabits/dockavel --ignore-platform-reqs');
    }
}
