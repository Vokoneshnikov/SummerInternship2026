<?php

namespace App\Command;

use App\Service\WeatherApiService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:get-weather')]
class WeatherCommand extends Command {

    public function __construct(private WeatherApiService $weatherApiService) {
        parent::__construct();
    }

    protected function configure() {
        $this->setName('app:get-weather')
            ->setDescription('Get the weather data')
            ->addArgument('city', InputArgument::OPTIONAL, 'City name');
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('city') || !$input->hasArgument('city')) {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter the city name: ');
            $question->setValidator(function ($value) {
                if (empty($value)) {
                    throw new \Exception('City name cannot be empty');
                }
                return $value;
            });
            $city = $helper->ask($input, $output, $question);
            $input->setArgument('city', $city);
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $city = $input->getArgument('city');

        $io = new SymfonyStyle($input, $output);
        $io->title('Getting weather data for ' . $city);

        try {
            $data = $this->weatherApiService->getWeatherData($city);

            $output->writeln("Current temperature: {$data['main']['temp']}С, feels like {$data['main']['feels_like']}С");
            $output->writeln("Humidity: {$data['main']['humidity']}%");
            $output->writeln("Wind speed: {$data['wind']['speed']}m/s");
        }
        catch (\Exception $e) {
            $io->error($e->getMessage());
        }



        return Command::SUCCESS;
    }


}
