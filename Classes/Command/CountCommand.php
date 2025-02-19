<?php

declare(strict_types=1);

namespace Ayacoo\VideoValidator\Command;

use Ayacoo\VideoValidator\Domain\Repository\FileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class CountCommand extends Command
{
    private ?LocalizationUtility $localizationUtility;

    private ?FileRepository $fileRepository;

    protected function configure(): void
    {
        $this->setDescription('Counts all videos of a media extension');
        $this->addArgument(
            'extension',
            InputArgument::REQUIRED,
            'e.g. Youtube',
        );
    }

    /**
     * @param LocalizationUtility|null $localizationUtility
     * @param FileRepository|null $fileRepository
     */
    public function __construct(
        LocalizationUtility $localizationUtility = null,
        FileRepository      $fileRepository = null
    )
    {
        $this->localizationUtility = $localizationUtility;
        $this->fileRepository = $fileRepository;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $extension = $input->getArgument('extension');

        $allowedExtensions = array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'] ?? []);
        if (in_array(strtolower($extension), $allowedExtensions, true)) {
            $numberOfVideos = count($this->fileRepository->getVideosByExtension($extension, time(), 0));
            $io->info(
                $this->localizationUtility::translate('count.numberOfVideos', 'video_validator') .
                ' ' . $extension . ': ' . $numberOfVideos
            );
        } else {
            $io->warning(
                $this->localizationUtility::translate('validation.extension.noSupport', 'video_validator')
            );
        }

        return 0;
    }
}
