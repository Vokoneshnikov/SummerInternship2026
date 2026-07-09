<?php

namespace App\Command;

use App\Service\ProductService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand("app:update-prices")]
class UpdatePricesCommand extends Command {

    public function __construct(private ProductService $productService) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int {
        try {
            $io = new SymfonyStyle($input, $output);
            $this->productService->updateProducts();

            return Command::SUCCESS;
        }
        catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
