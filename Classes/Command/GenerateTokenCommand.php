<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Command;

use Fr\ApiToken\Domain\Repository\TokenRepository;
use Fr\ApiToken\Service\TokenBuildService;
use Fr\ApiToken\Service\TokenService;
use Fr\ApiToken\Service\TokenServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates API credentials.
 * Puts out identifier + token pair and persists identifier and hash value into database
 *
 * Usage:
 * ./vendor/bin/typo3cms apitoken:generate
 *
 * Asks for token name,  a descriptive help to identify record, no technical usage.
 * Asks for token description, a descriptive help to identify record, no technical usage.
 *
 * Puts out:
    Your token was successfully generated.

    Identifier: 4a6*******d

    Secret: 7a5-*****c82

    (Please keep information safely and secure. Token is shown only once.)

 * Persists name, description, identifier, date of expiration (1 year) and hash value to verify token by API call.
 *
 */
class GenerateTokenCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var ?TokenService
     */
    protected ?TokenService $tokenService;

    /**
     * @param ?TokenRepository $repository
     */
    protected ?TokenRepository $repository;

    /**
     * @var ?TokenBuildService
     */
    protected ?TokenBuildService $tokenBuildService;


    /**
     * {@inheritDoc}
     *
     * @param string|null $name
     * @param TokenService|null $tokenService
     */
    public function __construct(string $name = null, TokenBuildService $tokenBuildService = null, TokenService $tokenService = null, TokenRepository $repository = null)
    {
        parent::__construct($name);

        $this->tokenService = $tokenService ?? GeneralUtility::makeInstance(TokenService::class);
        $this->tokenBuildService = $tokenBuildService ?? GeneralUtility::makeInstance(TokenBuildService::class);
        $this->repository = $repository ??  GeneralUtility::makeInstance(TokenRepository::class);
    }

    protected function configure(): void
    {
        $this->setDescription('Generate REST API access token.');

        $this->addOption(
            'name',
            null,
            InputOption::VALUE_OPTIONAL,
            'Name of the token'
        );
        $this->addOption(
            'description',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Optional token description'
        );
        $this->addOption(
            'json',
            'j',
            InputOption::VALUE_NONE,
            'Output data as JSON'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        // Parse options
        $name = $input->getOption('name') ?? null;
        $description = $input->getOption('description') ?? null;
        $json = (bool) $input->getOption('json');

        // Process "name" option
        $name = $this->processName($name);
        if ($name === null) {
            $message = sprintf('Name "%s" is not valid. Please enter a correct name.', $name);
            if (!$json) {
                $this->io->error($message);
            } else {
                $this->io->writeln(json_encode(['error' => $message]));
            }
            return 1;
        }
        if (!$json) {
            $this->io->success('Selected name: ' . $name);
        }

        // Process "description" option
        $description = $this->processDescription($description);
        if ($description !== '' && !$json) {
            $this->io->success('Selected description: ' . $description);
        }

        // Generate secret and identifier
        $secret = $this->tokenService->generateSecret();
        $identifier = $this->tokenService->generateIdentifier();
        $hash = $this->tokenService->hash($secret);

        $this->repository->persistNewToken(
            $this->tokenBuildService->buildInitialToken($name,
                $description,
                $identifier,
                $hash)
        );

        if (!$json) {
            $this->io->success([
                'Your token was successfully generated.',
                'Identifier: ' . $identifier,
                'Secret: ' . $secret,
                '(Please keep information safely and secure. Token is shown only once.)'
            ]);
        } else {
            $this->io->writeln(json_encode([
                'name' => $name,
                'description' => $description,
                'identifier' => $identifier,
                'secret' => $secret,
            ], JSON_THROW_ON_ERROR));
        }

        return 0;
    }

    /**
     * @param string|null $name
     * @return string|null
     */
    protected function processName(string $name = null): ?string
    {
        if ($name === null) {
            $question = new Question('Please enter the token name');
            $question->setValidator(function ($input) {
                if ($input === null || trim((string) $input) === '') {
                    throw new \InvalidArgumentException('Please enter a valid name.');
                }
                return (string) $input;
            });
            return $this->io->askQuestion($question);
        }
        if (trim($name) === '') {
            return null;
        }
        return $name;
    }

    /**
     * @param string|null $description
     * @return string
     */
    protected function processDescription(string $description = null): string
    {
        if ($description === null) {
            $question = new Question('Please enter a description for the token', '');
            $description = (string) $this->io->askQuestion($question);
        }
        return $description;
    }
}