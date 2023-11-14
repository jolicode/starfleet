<?php

// castor version: v0.9.1
namespace Castor\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AsArgument extends AsCommandArgument
{
    /**
     * @param array<string> $suggestedValues
     */
    public function __construct(string $name = null, public readonly string $description = '', public readonly array $suggestedValues = [])
    {
    }
}
namespace Castor\Attribute;

abstract class AsCommandArgument
{
    public function __construct(public readonly string|null $name = null)
    {
    }
}
namespace Castor\Attribute;

#[\Attribute(\Attribute::TARGET_FUNCTION)]
class AsContext
{
    public function __construct(public string $name = '', public bool $default = false)
    {
    }
}
namespace Castor\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AsOption extends AsCommandArgument
{
    /**
     * @param string|array<string>|null $shortcut
     * @param array<string>             $suggestedValues
     */
    public function __construct(string $name = null, public readonly string|array|null $shortcut = null, public readonly int|null $mode = null, public readonly string $description = '', public readonly array $suggestedValues = [])
    {
    }
}
namespace Castor\Attribute;

#[\Attribute(\Attribute::TARGET_FUNCTION)]
class AsTask
{
    /**
     * @param array<string>                          $aliases
     * @param array<int, callable(int): (int|false)> $onSignals
     */
    public function __construct(public string $name = '', public string|null $namespace = null, public string $description = '', public array $aliases = [], public array $onSignals = [], public string|bool $enabled = true)
    {
    }
}
namespace Castor;

class Context implements \ArrayAccess
{
    public readonly string $currentDirectory;
    /**
     * @phpstan-param ContextData $data The input parameter accepts an array or an Object
     *
     * @param array<string, string|\Stringable|int> $environment A list of environment variables to add to the command
     */
    public function __construct(public readonly array $data = [], public readonly array $environment = [], string $currentDirectory = null, public readonly bool $tty = false, public readonly bool $pty = true, public readonly float|null $timeout = 60, public readonly bool $quiet = false, public readonly bool $allowFailure = false, public readonly bool $notify = false, public readonly VerbosityLevel $verbosityLevel = VerbosityLevel::NOT_CONFIGURED)
    {
    }
    /** @param array<(int|string), mixed> $data */
    public function withData(array $data, bool $keepExisting = true) : self
    {
    }
    /** @param array<string, string|\Stringable|int> $environment */
    public function withEnvironment(array $environment, bool $keepExisting = true) : self
    {
    }
    public function withPath(string $path) : self
    {
    }
    public function withTty(bool $tty = true) : self
    {
    }
    public function withPty(bool $pty = true) : self
    {
    }
    public function withTimeout(float|null $timeout) : self
    {
    }
    public function withQuiet(bool $quiet = true) : self
    {
    }
    public function withAllowFailure(bool $allowFailure = true) : self
    {
    }
    public function withNotify(bool $notify = true) : self
    {
    }
    public function withVerbosityLevel(VerbosityLevel $verbosityLevel) : self
    {
    }
    public function offsetExists(mixed $offset) : bool
    {
    }
    public function offsetGet(mixed $offset) : mixed
    {
    }
    public function offsetSet(mixed $offset, mixed $value) : void
    {
    }
    public function offsetUnset(mixed $offset) : void
    {
    }
}
namespace Castor\Fingerprint;

enum FileHashStrategy
{
    case Content;
    case MTimes;
}
namespace Castor\Fingerprint;

class FingerprintHelper
{
    private const SUFFIX = '.fingerprint';
    public static function verifyFingerprintFromHash(string $fingerprint) : bool
    {
    }
    public static function postProcessFingerprintForHash(string $hash) : void
    {
    }
}
namespace Castor;

class GlobalHelper
{
    private static \Castor\Console\Application $application;
    private static \Symfony\Component\Console\Input\InputInterface $input;
    private static SectionOutput $sectionOutput;
    private static \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle;
    private static \Monolog\Logger $logger;
    private static ContextRegistry $contextRegistry;
    private static \Symfony\Component\Console\Command\Command $command;
    private static Context $initialContext;
    private static \Symfony\Component\Filesystem\Filesystem $fs;
    private static \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient;
    private static \Psr\Cache\CacheItemPoolInterface&\Symfony\Contracts\Cache\CacheInterface $cache;
    private static \Symfony\Component\ExpressionLanguage\ExpressionLanguage $expressionLanguage;
    public static function setApplication(\Castor\Console\Application $application) : void
    {
    }
    public static function getApplication() : \Castor\Console\Application
    {
    }
    public static function setInput(\Symfony\Component\Console\Input\InputInterface $input) : void
    {
    }
    public static function getInput() : \Symfony\Component\Console\Input\InputInterface
    {
    }
    public static function getOutput() : \Symfony\Component\Console\Output\OutputInterface
    {
    }
    public static function setSectionOutput(SectionOutput $output) : void
    {
    }
    public static function getSectionOutput() : SectionOutput
    {
    }
    public static function getSymfonyStyle() : \Symfony\Component\Console\Style\SymfonyStyle
    {
    }
    public static function setLogger(\Monolog\Logger $logger) : void
    {
    }
    public static function getLogger() : \Monolog\Logger
    {
    }
    public static function setContextRegistry(ContextRegistry $contextRegistry) : void
    {
    }
    public static function getContextRegistry() : ContextRegistry
    {
    }
    public static function setCommand(\Symfony\Component\Console\Command\Command $command) : void
    {
    }
    public static function setInitialContext(Context $initialContext) : void
    {
    }
    public static function getInitialContext() : Context
    {
    }
    public static function getContext(string $name = null) : Context
    {
    }
    /**
     * @template TKey of key-of<ContextData>
     * @template TDefault
     *
     * @param TKey|string $key
     * @param TDefault    $default
     *
     * @phpstan-return ($key is TKey ? ContextData[TKey] : TDefault)
     */
    public static function getVariable(string $key, mixed $default = null) : mixed
    {
    }
    public static function getCommand() : \Symfony\Component\Console\Command\Command
    {
    }
    public static function getFilesystem() : \Symfony\Component\Filesystem\Filesystem
    {
    }
    public static function setHttpClient(\Symfony\Contracts\HttpClient\HttpClientInterface $httpClient) : void
    {
    }
    public static function getHttpClient() : \Symfony\Contracts\HttpClient\HttpClientInterface
    {
    }
    public static function setCache(\Psr\Cache\CacheItemPoolInterface&\Symfony\Contracts\Cache\CacheInterface $cache) : void
    {
    }
    public static function getCache() : \Psr\Cache\CacheItemPoolInterface&\Symfony\Contracts\Cache\CacheInterface
    {
    }
    public static function setupDefaultCache() : void
    {
    }
    public static function getExpressionLanguage() : \Symfony\Component\ExpressionLanguage\ExpressionLanguage
    {
    }
}
namespace Castor;

class HasherHelper
{
    private \HashContext $hashContext;
    /**
     * @see https://www.php.net/manual/en/function.hash-algos.php
     */
    public function __construct(string $algo = 'xxh128')
    {
    }
    public function write(string $value) : self
    {
    }
    public function writeFile(string $path, \Castor\Fingerprint\FileHashStrategy $strategy = \Castor\Fingerprint\FileHashStrategy::MTimes) : self
    {
    }
    public function writeWithFinder(\Symfony\Component\Finder\Finder $finder, \Castor\Fingerprint\FileHashStrategy $strategy = \Castor\Fingerprint\FileHashStrategy::MTimes) : self
    {
    }
    public function writeGlob(string $pattern, \Castor\Fingerprint\FileHashStrategy $strategy = \Castor\Fingerprint\FileHashStrategy::MTimes) : self
    {
    }
    public function writeTaskName() : self
    {
    }
    public function writeTaskArgs(string ...$args) : self
    {
    }
    public function writeTask() : self
    {
    }
    public function finish() : string
    {
    }
}
namespace Castor\Monolog\Processor;

class ProcessProcessor implements \Monolog\Processor\ProcessorInterface
{
    public function __invoke(\Monolog\LogRecord $record) : \Monolog\LogRecord
    {
    }
    /**
     * @return array{cwd: ?string, env: array<string, string>, runnable: string}
     */
    private function formatProcess(\Symfony\Component\Process\Process $process) : array
    {
    }
}
namespace Castor;

class PathHelper
{
    public static function getRoot() : string
    {
    }
    public static function realpath(string $path) : string
    {
    }
}
namespace Castor;

class PlatformUtil extends \Joli\JoliNotif\Util\OsHelper
{
    public static function getEnv(string $name) : string|false
    {
    }
    /**
     * @throws \RuntimeException If the user home could not reliably be determined
     */
    public static function getUserDirectory() : string
    {
    }
}
namespace Castor;

class SectionDetails
{
    public function __construct(public \Symfony\Component\Console\Output\ConsoleSectionOutput $section, public \Symfony\Component\Console\Output\ConsoleSectionOutput $progressBarSection, public float $start, public string $index)
    {
    }
}
namespace Castor;

enum VerbosityLevel : int
{
    case NOT_CONFIGURED = -1;
    case QUIET = 0;
    case NORMAL = 1;
    case VERBOSE = 2;
    case VERY_VERBOSE = 3;
    case DEBUG = 4;
    public static function fromSymfonyOutput(\Symfony\Component\Console\Output\OutputInterface $output) : self
    {
    }
    public function isNotConfigured() : bool
    {
    }
    public function isQuiet() : bool
    {
    }
    public function isVerbose() : bool
    {
    }
    public function isVeryVerbose() : bool
    {
    }
    public function isDebug() : bool
    {
    }
}
namespace Castor;

/**
 * @return array<mixed>
 */
function parallel(callable ...$callbacks) : array
{
}
/**
 * @param string|array<string|\Stringable|int>           $command
 * @param array<string, string|\Stringable|int>|null     $environment
 * @param (callable(string, string, Process) :void)|null $callback
 */
function run(string|array $command, array $environment = null, string $path = null, bool $tty = null, bool $pty = null, float $timeout = null, bool $quiet = null, bool $allowFailure = null, bool $notify = null, callable $callback = null, Context $context = null) : \Symfony\Component\Process\Process
{
}
/**
 * @param string|array<string|\Stringable|int>       $command
 * @param array<string, string|\Stringable|int>|null $environment
 */
function capture(string|array $command, array $environment = null, string $path = null, float $timeout = null, bool $allowFailure = null, string $onFailure = null, Context $context = null) : string
{
}
/**
 * @param string|array<string|\Stringable|int>       $command
 * @param array<string, string|\Stringable|int>|null $environment
 */
function exit_code(string|array $command, array $environment = null, string $path = null, float $timeout = null, bool $quiet = null, Context $context = null) : int
{
}
function get_exit_code(...$args) : int
{
}
/**
 * This function is considered experimental and may change in the future.
 *
 * @param array{
 *     'port'?: int,
 *     'path_private_key'?: string,
 *     'jump_host'?: string,
 *     'multiplexing_control_path'?: string,
 *     'multiplexing_control_persist'?: string,
 *     'enable_strict_check'?: bool,
 *     'password_authentication'?: bool,
 * } $sshOptions
 */
function ssh(string $command, string $host, string $user, array $sshOptions = [], string $path = null, bool $quiet = null, bool $allowFailure = null, bool $notify = null, float $timeout = null) : \Symfony\Component\Process\Process
{
}
function notify(string $message) : void
{
}
/**
 * @param string|non-empty-array<string>                 $path
 * @param (callable(string, string) : (false|void|null)) $function
 */
function watch(string|array $path, callable $function, Context $context = null) : void
{
}
/**
 * @param array<string, mixed> $context
 *
 * @phpstan-param \Monolog\Level|\Psr\Log\LogLevel::* $level
 */
function log(string|\Stringable $message, mixed $level = 'info', array $context = []) : void
{
}
function app() : \Castor\Console\Application
{
}
function get_application() : \Castor\Console\Application
{
}
function input() : \Symfony\Component\Console\Input\InputInterface
{
}
function get_input() : \Symfony\Component\Console\Input\InputInterface
{
}
function output() : \Symfony\Component\Console\Output\OutputInterface
{
}
function get_output() : \Symfony\Component\Console\Output\OutputInterface
{
}
function io() : \Symfony\Component\Console\Style\SymfonyStyle
{
}
function add_context(string $name, \Closure $callable, bool $default = false) : void
{
}
function context(string $name = null) : Context
{
}
function get_context() : Context
{
}
/**
 * @template TKey of key-of<ContextData>
 * @template TDefault
 *
 * @param TKey|string $key
 * @param TDefault    $default
 *
 * @phpstan-return ($key is TKey ? ContextData[TKey] : TDefault)
 */
function variable(string $key, mixed $default = null) : mixed
{
}
function task() : \Symfony\Component\Console\Command\Command
{
}
function get_command() : \Symfony\Component\Console\Command\Command
{
}
function fs() : \Symfony\Component\Filesystem\Filesystem
{
}
function finder() : \Symfony\Component\Finder\Finder
{
}
/**
 * @param string                                                                                      $key The key of the item to retrieve from the cache
 * @param (callable(CacheItemInterface,bool):T)|(callable(ItemInterface,bool):T)|CallbackInterface<T> $or  Use this callback to compute the value
 *
 * @return T
 *
 * @see CacheInterface::get()
 *
 * @template T
 */
function cache(string $key, callable $or) : mixed
{
}
function get_cache() : \Psr\Cache\CacheItemPoolInterface&\Symfony\Contracts\Cache\CacheInterface
{
}
/**
 * @param array<string, mixed> $options
 *
 * @see HttpClientInterface::OPTIONS_DEFAULTS
 */
function request(string $method, string $url, array $options = []) : \Symfony\Contracts\HttpClient\ResponseInterface
{
}
function http_client() : \Symfony\Contracts\HttpClient\HttpClientInterface
{
}
function import(string $path) : void
{
}
function fix_exception(\Exception $exception) : \Exception
{
}
/**
 * @return array<string, mixed>
 */
function load_dot_env(string $path = null) : array
{
}
/**
 * @template T
 *
 * @param (callable(Context) :T)                     $callback
 * @param array<string, string|\Stringable|int>|null $data
 * @param array<string, string|\Stringable|int>|null $environment
 */
function with(callable $callback, array $data = null, array $environment = null, string $path = null, bool $tty = null, bool $pty = null, float $timeout = null, bool $quiet = null, bool $allowFailure = null, bool $notify = null, Context|string $context = null) : mixed
{
}
/**
 * @see https://www.php.net/manual/en/function.hash-algos.php
 */
function hasher(string $algo = 'xxh128') : HasherHelper
{
}
function fingerprint_exists(string $fingerprint) : bool
{
}
function fingerprint_save(string $fingerprint) : void
{
}
function fingerprint(callable $callback, string $fingerprint) : void
{
}
namespace Symfony\Component\Console;

/**
 * An Application is the container for a collection of commands.
 *
 * It is the main entry point of a Console application.
 *
 * This class is optimized for a standard CLI environment.
 *
 * Usage:
 *
 *     $app = new Application('myapp', '1.0 (stable)');
 *     $app->add(new SimpleCommand());
 *     $app->run();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Application implements \Symfony\Contracts\Service\ResetInterface
{
    private array $commands = [];
    private bool $wantHelps = false;
    private ?\Symfony\Component\Console\Command\Command $runningCommand = null;
    private string $name;
    private string $version;
    private ?\Symfony\Component\Console\CommandLoader\CommandLoaderInterface $commandLoader = null;
    private bool $catchExceptions = true;
    private bool $catchErrors = false;
    private bool $autoExit = true;
    private \Symfony\Component\Console\Input\InputDefinition $definition;
    private \Symfony\Component\Console\Helper\HelperSet $helperSet;
    private ?\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher = null;
    private Terminal $terminal;
    private string $defaultCommand;
    private bool $singleCommand = false;
    private bool $initialized = false;
    private ?\Symfony\Component\Console\SignalRegistry\SignalRegistry $signalRegistry = null;
    private array $signalsToDispatchEvent = [];
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
    }
    /**
     * @final
     */
    public function setDispatcher(\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher) : void
    {
    }
    /**
     * @return void
     */
    public function setCommandLoader(\Symfony\Component\Console\CommandLoader\CommandLoaderInterface $commandLoader)
    {
    }
    public function getSignalRegistry() : \Symfony\Component\Console\SignalRegistry\SignalRegistry
    {
    }
    /**
     * @return void
     */
    public function setSignalsToDispatchEvent(int ...$signalsToDispatchEvent)
    {
    }
    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws \Exception When running fails. Bypass this when {@link setCatchExceptions()}.
     */
    public function run(\Symfony\Component\Console\Input\InputInterface $input = null, \Symfony\Component\Console\Output\OutputInterface $output = null) : int
    {
    }
    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
    }
    /**
     * @return void
     */
    public function reset()
    {
    }
    /**
     * @return void
     */
    public function setHelperSet(\Symfony\Component\Console\Helper\HelperSet $helperSet)
    {
    }
    public function getHelperSet() : \Symfony\Component\Console\Helper\HelperSet
    {
    }
    /**
     * @return void
     */
    public function setDefinition(\Symfony\Component\Console\Input\InputDefinition $definition)
    {
    }
    public function getDefinition() : \Symfony\Component\Console\Input\InputDefinition
    {
    }
    public function complete(\Symfony\Component\Console\Completion\CompletionInput $input, \Symfony\Component\Console\Completion\CompletionSuggestions $suggestions) : void
    {
    }
    public function getHelp() : string
    {
    }
    public function areExceptionsCaught() : bool
    {
    }
    /**
     * Sets whether to catch exceptions or not during commands execution.
     *
     * @return void
     */
    public function setCatchExceptions(bool $boolean)
    {
    }
    public function setCatchErrors(bool $catchErrors = true) : void
    {
    }
    public function isAutoExitEnabled() : bool
    {
    }
    /**
     * Sets whether to automatically exit after a command execution or not.
     *
     * @return void
     */
    public function setAutoExit(bool $boolean)
    {
    }
    public function getName() : string
    {
    }
    /**
     * Sets the application name.
     *
     * @return void
     */
    public function setName(string $name)
    {
    }
    public function getVersion() : string
    {
    }
    /**
     * Sets the application version.
     *
     * @return void
     */
    public function setVersion(string $version)
    {
    }
    /**
     * Returns the long version of the application.
     *
     * @return string
     */
    public function getLongVersion()
    {
    }
    public function register(string $name) : \Symfony\Component\Console\Command\Command
    {
    }
    /**
     * Adds an array of command objects.
     *
     * If a Command is not enabled it will not be added.
     *
     * @param Command[] $commands An array of commands
     *
     * @return void
     */
    public function addCommands(array $commands)
    {
    }
    /**
     * Adds a command object.
     *
     * If a command with the same name already exists, it will be overridden.
     * If the command is not enabled it will not be added.
     *
     * @return Command|null
     */
    public function add(\Symfony\Component\Console\Command\Command $command)
    {
    }
    /**
     * Returns a registered command by name or alias.
     *
     * @return Command
     *
     * @throws CommandNotFoundException When given command name does not exist
     */
    public function get(string $name)
    {
    }
    public function has(string $name) : bool
    {
    }
    /**
     * Returns an array of all unique namespaces used by currently registered commands.
     *
     * It does not return the global namespace which always exists.
     *
     * @return string[]
     */
    public function getNamespaces() : array
    {
    }
    /**
     * Finds a registered namespace by a name or an abbreviation.
     *
     * @throws NamespaceNotFoundException When namespace is incorrect or ambiguous
     */
    public function findNamespace(string $namespace) : string
    {
    }
    /**
     * Finds a command by name or alias.
     *
     * Contrary to get, this command tries to find the best
     * match if you give it an abbreviation of a name or alias.
     *
     * @return Command
     *
     * @throws CommandNotFoundException When command name is incorrect or ambiguous
     */
    public function find(string $name)
    {
    }
    /**
     * Gets the commands (registered in the given namespace if provided).
     *
     * The array keys are the full names and the values the command instances.
     *
     * @return Command[]
     */
    public function all(string $namespace = null)
    {
    }
    /**
     * Returns an array of possible abbreviations given a set of names.
     *
     * @return string[][]
     */
    public static function getAbbreviations(array $names) : array
    {
    }
    public function renderThrowable(\Throwable $e, \Symfony\Component\Console\Output\OutputInterface $output) : void
    {
    }
    protected function doRenderThrowable(\Throwable $e, \Symfony\Component\Console\Output\OutputInterface $output) : void
    {
    }
    /**
     * Configures the input and output instances based on the user arguments and options.
     *
     * @return void
     */
    protected function configureIO(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
    }
    /**
     * Runs the current command.
     *
     * If an event dispatcher has been attached to the application,
     * events are also dispatched during the life-cycle of the command.
     *
     * @return int 0 if everything went fine, or an error code
     */
    protected function doRunCommand(\Symfony\Component\Console\Command\Command $command, \Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
    }
    protected function getCommandName(\Symfony\Component\Console\Input\InputInterface $input) : ?string
    {
    }
    protected function getDefaultInputDefinition() : \Symfony\Component\Console\Input\InputDefinition
    {
    }
    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[]
     */
    protected function getDefaultCommands() : array
    {
    }
    protected function getDefaultHelperSet() : \Symfony\Component\Console\Helper\HelperSet
    {
    }
    private function getAbbreviationSuggestions(array $abbrevs) : string
    {
    }
    public function extractNamespace(string $name, int $limit = null) : string
    {
    }
    /**
     * Finds alternative of $name among $collection,
     * if nothing is found in $collection, try in $abbrevs.
     *
     * @return string[]
     */
    private function findAlternatives(string $name, iterable $collection) : array
    {
    }
    /**
     * Sets the default Command name.
     *
     * @return $this
     */
    public function setDefaultCommand(string $commandName, bool $isSingleCommand = false) : static
    {
    }
    private function splitStringByWidth(string $string, int $width) : array
    {
    }
    /**
     * Returns all namespaces of the command name.
     *
     * @return string[]
     */
    private function extractAllNamespaces(string $name) : array
    {
    }
    private function init() : void
    {
    }
}
namespace Symfony\Component\Console\Input;

/**
 * Represents a command line argument.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputArgument
{
    public const REQUIRED = 1;
    public const OPTIONAL = 2;
    public const IS_ARRAY = 4;
    private string $name;
    private int $mode;
    private string|int|bool|array|null|float $default;
    private array|\Closure $suggestedValues;
    private string $description;
    /**
     * @param string                                                                        $name            The argument name
     * @param int|null                                                                      $mode            The argument mode: a bit mask of self::REQUIRED, self::OPTIONAL and self::IS_ARRAY
     * @param string                                                                        $description     A description text
     * @param string|bool|int|float|array|null                                              $default         The default value (for self::OPTIONAL mode only)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @throws InvalidArgumentException When argument mode is not valid
     */
    public function __construct(string $name, int $mode = null, string $description = '', string|bool|int|float|array $default = null, \Closure|array $suggestedValues = [])
    {
    }
    public function getName() : string
    {
    }
    /**
     * Returns true if the argument is required.
     *
     * @return bool true if parameter mode is self::REQUIRED, false otherwise
     */
    public function isRequired() : bool
    {
    }
    /**
     * Returns true if the argument can take multiple values.
     *
     * @return bool true if mode is self::IS_ARRAY, false otherwise
     */
    public function isArray() : bool
    {
    }
    /**
     * Sets the default value.
     *
     * @return void
     *
     * @throws LogicException When incorrect default value is given
     */
    public function setDefault(string|bool|int|float|array $default = null)
    {
    }
    public function getDefault() : string|bool|int|float|array|null
    {
    }
    public function hasCompletion() : bool
    {
    }
    /**
     * Adds suggestions to $suggestions for the current completion input.
     *
     * @see Command::complete()
     */
    public function complete(\Symfony\Component\Console\Completion\CompletionInput $input, \Symfony\Component\Console\Completion\CompletionSuggestions $suggestions) : void
    {
    }
    public function getDescription() : string
    {
    }
}
namespace Symfony\Component\Console\Input;

/**
 * InputInterface is the interface implemented by all input classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @method string __toString() Returns a stringified representation of the args passed to the command.
 *                             InputArguments MUST be escaped as well as the InputOption values passed to the command.
 */
interface InputInterface
{
    public function getFirstArgument() : ?string;
    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     * Does not necessarily return the correct result for short options
     * when multiple flags are combined in the same option.
     *
     * @param string|array $values     The values to look for in the raw parameters (can be an array)
     * @param bool         $onlyParams Only check real parameters, skip those following an end of options (--) signal
     */
    public function hasParameterOption(string|array $values, bool $onlyParams = false) : bool;
    /**
     * Returns the value of a raw option (not parsed).
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     * Does not necessarily return the correct result for short options
     * when multiple flags are combined in the same option.
     *
     * @param string|array                     $values     The value(s) to look for in the raw parameters (can be an array)
     * @param string|bool|int|float|array|null $default    The default value to return if no result is found
     * @param bool                             $onlyParams Only check real parameters, skip those following an end of options (--) signal
     *
     * @return mixed
     */
    public function getParameterOption(string|array $values, string|bool|int|float|array|null $default = false, bool $onlyParams = false);
    /**
     * Binds the current Input instance with the given arguments and options.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function bind(InputDefinition $definition);
    /**
     * Validates the input.
     *
     * @return void
     *
     * @throws RuntimeException When not enough arguments are given
     */
    public function validate();
    /**
     * Returns all the given arguments merged with the default values.
     *
     * @return array<string|bool|int|float|array|null>
     */
    public function getArguments() : array;
    /**
     * Returns the argument value for a given argument name.
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function getArgument(string $name);
    /**
     * Sets an argument value by name.
     *
     * @return void
     *
     * @throws InvalidArgumentException When argument given doesn't exist
     */
    public function setArgument(string $name, mixed $value);
    public function hasArgument(string $name) : bool;
    /**
     * Returns all the given options merged with the default values.
     *
     * @return array<string|bool|int|float|array|null>
     */
    public function getOptions() : array;
    /**
     * Returns the option value for a given option name.
     *
     * @return mixed
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function getOption(string $name);
    /**
     * Sets an option value by name.
     *
     * @return void
     *
     * @throws InvalidArgumentException When option given doesn't exist
     */
    public function setOption(string $name, mixed $value);
    public function hasOption(string $name) : bool;
    public function isInteractive() : bool;
    /**
     * Sets the input interactivity.
     *
     * @return void
     */
    public function setInteractive(bool $interactive);
}
namespace Symfony\Component\Console\Input;

/**
 * Represents a command line option.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class InputOption
{
    public const VALUE_NONE = 1;
    public const VALUE_REQUIRED = 2;
    public const VALUE_OPTIONAL = 4;
    public const VALUE_IS_ARRAY = 8;
    public const VALUE_NEGATABLE = 16;
    private string $name;
    private string|array|null $shortcut;
    private int $mode;
    private string|int|bool|array|null|float $default;
    private array|\Closure $suggestedValues;
    private string $description;
    /**
     * @param string|array|null                                                             $shortcut        The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param int|null                                                                      $mode            The option mode: One of the VALUE_* constants
     * @param string|bool|int|float|array|null                                              $default         The default value (must be null for self::VALUE_NONE)
     * @param array|\Closure(CompletionInput,CompletionSuggestions):list<string|Suggestion> $suggestedValues The values used for input completion
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     */
    public function __construct(string $name, string|array $shortcut = null, int $mode = null, string $description = '', string|bool|int|float|array $default = null, array|\Closure $suggestedValues = [])
    {
    }
    public function getShortcut() : ?string
    {
    }
    public function getName() : string
    {
    }
    /**
     * Returns true if the option accepts a value.
     *
     * @return bool true if value mode is not self::VALUE_NONE, false otherwise
     */
    public function acceptValue() : bool
    {
    }
    /**
     * Returns true if the option requires a value.
     *
     * @return bool true if value mode is self::VALUE_REQUIRED, false otherwise
     */
    public function isValueRequired() : bool
    {
    }
    /**
     * Returns true if the option takes an optional value.
     *
     * @return bool true if value mode is self::VALUE_OPTIONAL, false otherwise
     */
    public function isValueOptional() : bool
    {
    }
    /**
     * Returns true if the option can take multiple values.
     *
     * @return bool true if mode is self::VALUE_IS_ARRAY, false otherwise
     */
    public function isArray() : bool
    {
    }
    public function isNegatable() : bool
    {
    }
    /**
     * @return void
     */
    public function setDefault(string|bool|int|float|array $default = null)
    {
    }
    public function getDefault() : string|bool|int|float|array|null
    {
    }
    public function getDescription() : string
    {
    }
    public function hasCompletion() : bool
    {
    }
    /**
     * Adds suggestions to $suggestions for the current completion input.
     *
     * @see Command::complete()
     */
    public function complete(\Symfony\Component\Console\Completion\CompletionInput $input, \Symfony\Component\Console\Completion\CompletionSuggestions $suggestions) : void
    {
    }
    public function equals(self $option) : bool
    {
    }
}
namespace Symfony\Component\Console\Output;

/**
 * OutputInterface is the interface implemented by all Output classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface OutputInterface
{
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;
    public const VERBOSITY_VERY_VERBOSE = 128;
    public const VERBOSITY_DEBUG = 256;
    public const OUTPUT_NORMAL = 1;
    public const OUTPUT_RAW = 2;
    public const OUTPUT_PLAIN = 4;
    /**
     * Writes a message to the output.
     *
     * @param bool $newline Whether to add a newline
     * @param int  $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                      0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = 0);
    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param int $options A bitmask of options (one of the OUTPUT or VERBOSITY constants),
     *                     0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return void
     */
    public function writeln(string|iterable $messages, int $options = 0);
    /**
     * Sets the verbosity of the output.
     *
     * @param self::VERBOSITY_* $level
     *
     * @return void
     */
    public function setVerbosity(int $level);
    /**
     * Gets the current verbosity of the output.
     *
     * @return self::VERBOSITY_*
     */
    public function getVerbosity() : int;
    public function isQuiet() : bool;
    public function isVerbose() : bool;
    public function isVeryVerbose() : bool;
    public function isDebug() : bool;
    /**
     * Sets the decorated flag.
     *
     * @return void
     */
    public function setDecorated(bool $decorated);
    public function isDecorated() : bool;
    /**
     * @return void
     */
    public function setFormatter(\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter);
    public function getFormatter() : \Symfony\Component\Console\Formatter\OutputFormatterInterface;
}
namespace Symfony\Component\Console\Style;

/**
 * Output decorator helpers for the Symfony Style Guide.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyStyle extends OutputStyle
{
    public const MAX_LINE_LENGTH = 120;
    private \Symfony\Component\Console\Input\InputInterface $input;
    private \Symfony\Component\Console\Output\OutputInterface $output;
    private \Symfony\Component\Console\Helper\SymfonyQuestionHelper $questionHelper;
    private \Symfony\Component\Console\Helper\ProgressBar $progressBar;
    private int $lineLength;
    private \Symfony\Component\Console\Output\TrimmedBufferOutput $bufferedOutput;
    public function __construct(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
    }
    /**
     * Formats a message as a block of text.
     *
     * @return void
     */
    public function block(string|array $messages, string $type = null, string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = true)
    {
    }
    /**
     * @return void
     */
    public function title(string $message)
    {
    }
    /**
     * @return void
     */
    public function section(string $message)
    {
    }
    /**
     * @return void
     */
    public function listing(array $elements)
    {
    }
    /**
     * @return void
     */
    public function text(string|array $message)
    {
    }
    /**
     * Formats a command comment.
     *
     * @return void
     */
    public function comment(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function success(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function error(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function warning(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function note(string|array $message)
    {
    }
    /**
     * Formats an info message.
     *
     * @return void
     */
    public function info(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function caution(string|array $message)
    {
    }
    /**
     * @return void
     */
    public function table(array $headers, array $rows)
    {
    }
    /**
     * Formats a horizontal table.
     *
     * @return void
     */
    public function horizontalTable(array $headers, array $rows)
    {
    }
    /**
     * Formats a list of key/value horizontally.
     *
     * Each row can be one of:
     * * 'A title'
     * * ['key' => 'value']
     * * new TableSeparator()
     *
     * @return void
     */
    public function definitionList(string|array|\Symfony\Component\Console\Helper\TableSeparator ...$list)
    {
    }
    public function ask(string $question, string $default = null, callable $validator = null) : mixed
    {
    }
    public function askHidden(string $question, callable $validator = null) : mixed
    {
    }
    public function confirm(string $question, bool $default = true) : bool
    {
    }
    public function choice(string $question, array $choices, mixed $default = null, bool $multiSelect = false) : mixed
    {
    }
    /**
     * @return void
     */
    public function progressStart(int $max = 0)
    {
    }
    /**
     * @return void
     */
    public function progressAdvance(int $step = 1)
    {
    }
    /**
     * @return void
     */
    public function progressFinish()
    {
    }
    public function createProgressBar(int $max = 0) : \Symfony\Component\Console\Helper\ProgressBar
    {
    }
    /**
     * @see ProgressBar::iterate()
     *
     * @template TKey
     * @template TValue
     *
     * @param iterable<TKey, TValue> $iterable
     * @param int|null               $max      Number of steps to complete the bar (0 if indeterminate), if null it will be inferred from $iterable
     *
     * @return iterable<TKey, TValue>
     */
    public function progressIterate(iterable $iterable, int $max = null) : iterable
    {
    }
    public function askQuestion(\Symfony\Component\Console\Question\Question $question) : mixed
    {
    }
    /**
     * @return void
     */
    public function writeln(string|iterable $messages, int $type = self::OUTPUT_NORMAL)
    {
    }
    /**
     * @return void
     */
    public function write(string|iterable $messages, bool $newline = false, int $type = self::OUTPUT_NORMAL)
    {
    }
    /**
     * @return void
     */
    public function newLine(int $count = 1)
    {
    }
    public function getErrorStyle() : self
    {
    }
    public function createTable() : \Symfony\Component\Console\Helper\Table
    {
    }
    private function getProgressBar() : \Symfony\Component\Console\Helper\ProgressBar
    {
    }
    private function autoPrependBlock() : void
    {
    }
    private function autoPrependText() : void
    {
    }
    private function writeBuffer(string $message, bool $newLine, int $type) : void
    {
    }
    private function createBlock(iterable $messages, string $type = null, string $style = null, string $prefix = ' ', bool $padding = false, bool $escape = false) : array
    {
    }
}
namespace Symfony\Component\Filesystem\Exception;

/**
 * Exception interface for all exceptions thrown by the component.
 *
 * @author Romain Neutron <imprec@gmail.com>
 */
interface ExceptionInterface extends \Throwable
{
}
namespace Symfony\Component\Filesystem;

/**
 * Provides basic utility to manipulate the file system.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Filesystem
{
    private static $lastError;
    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     * @return void
     *
     * @throws FileNotFoundException When originFile doesn't exist
     * @throws IOException           When copy fails
     */
    public function copy(string $originFile, string $targetFile, bool $overwriteNewerFiles = false)
    {
    }
    /**
     * Creates a directory recursively.
     *
     * @return void
     *
     * @throws IOException On any directory creation failure
     */
    public function mkdir(string|iterable $dirs, int $mode = 0777)
    {
    }
    public function exists(string|iterable $files) : bool
    {
    }
    /**
     * Sets access and modification time of file.
     *
     * @param int|null $time  The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param int|null $atime The access time as a Unix timestamp, if not supplied the current system time is used
     *
     * @return void
     *
     * @throws IOException When touch fails
     */
    public function touch(string|iterable $files, int $time = null, int $atime = null)
    {
    }
    /**
     * Removes files or directories.
     *
     * @return void
     *
     * @throws IOException When removal fails
     */
    public function remove(string|iterable $files)
    {
    }
    private static function doRemove(array $files, bool $isRecursive) : void
    {
    }
    /**
     * Change mode for an array of files or directories.
     *
     * @param int  $mode      The new mode (octal)
     * @param int  $umask     The mode mask (octal)
     * @param bool $recursive Whether change the mod recursively or not
     *
     * @return void
     *
     * @throws IOException When the change fails
     */
    public function chmod(string|iterable $files, int $mode, int $umask = 00, bool $recursive = false)
    {
    }
    /**
     * Change the owner of an array of files or directories.
     *
     * @param string|int $user      A user name or number
     * @param bool       $recursive Whether change the owner recursively or not
     *
     * @return void
     *
     * @throws IOException When the change fails
     */
    public function chown(string|iterable $files, string|int $user, bool $recursive = false)
    {
    }
    /**
     * Change the group of an array of files or directories.
     *
     * @param string|int $group     A group name or number
     * @param bool       $recursive Whether change the group recursively or not
     *
     * @return void
     *
     * @throws IOException When the change fails
     */
    public function chgrp(string|iterable $files, string|int $group, bool $recursive = false)
    {
    }
    /**
     * Renames a file or a directory.
     *
     * @return void
     *
     * @throws IOException When target file or directory already exists
     * @throws IOException When origin cannot be renamed
     */
    public function rename(string $origin, string $target, bool $overwrite = false)
    {
    }
    /**
     * Tells whether a file exists and is readable.
     *
     * @throws IOException When windows path is longer than 258 characters
     */
    private function isReadable(string $filename) : bool
    {
    }
    /**
     * Creates a symbolic link or copy a directory.
     *
     * @return void
     *
     * @throws IOException When symlink fails
     */
    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false)
    {
    }
    /**
     * Creates a hard link, or several hard links to a file.
     *
     * @param string|string[] $targetFiles The target file(s)
     *
     * @return void
     *
     * @throws FileNotFoundException When original file is missing or not a file
     * @throws IOException           When link fails, including if link already exists
     */
    public function hardlink(string $originFile, string|iterable $targetFiles)
    {
    }
    /**
     * @param string $linkType Name of the link type, typically 'symbolic' or 'hard'
     */
    private function linkException(string $origin, string $target, string $linkType) : never
    {
    }
    public function readlink(string $path, bool $canonicalize = false) : ?string
    {
    }
    public function makePathRelative(string $endPath, string $startPath) : string
    {
    }
    /**
     * Mirrors a directory to another.
     *
     * Copies files and directories from the origin directory into the target directory. By default:
     *
     *  - existing files in the target directory will be overwritten, except if they are newer (see the `override` option)
     *  - files in the target directory that do not exist in the source directory will not be deleted (see the `delete` option)
     *
     * @param \Traversable|null $iterator Iterator that filters which files and directories to copy, if null a recursive iterator is created
     * @param array             $options  An array of boolean options
     *                                    Valid options are:
     *                                    - $options['override'] If true, target files newer than origin files are overwritten (see copy(), defaults to false)
     *                                    - $options['copy_on_windows'] Whether to copy files instead of links on Windows (see symlink(), defaults to false)
     *                                    - $options['delete'] Whether to delete files that are not in the source directory (defaults to false)
     *
     * @return void
     *
     * @throws IOException When file type is unknown
     */
    public function mirror(string $originDir, string $targetDir, \Traversable $iterator = null, array $options = [])
    {
    }
    public function isAbsolutePath(string $file) : bool
    {
    }
    /**
     * Creates a temporary file with support for custom stream wrappers.
     *
     * @param string $prefix The prefix of the generated temporary filename
     *                       Note: Windows uses only the first three characters of prefix
     * @param string $suffix The suffix of the generated temporary filename
     *
     * @return string The new temporary filename (with path), or throw an exception on failure
     */
    public function tempnam(string $dir, string $prefix, string $suffix = '') : string
    {
    }
    /**
     * Atomically dumps content into a file.
     *
     * @param string|resource $content The data to write into the file
     *
     * @return void
     *
     * @throws IOException if the file cannot be written to
     */
    public function dumpFile(string $filename, $content)
    {
    }
    /**
     * Appends content to an existing file.
     *
     * @param string|resource $content The content to append
     * @param bool            $lock    Whether the file should be locked when writing to it
     *
     * @return void
     *
     * @throws IOException If the file is not writable
     */
    public function appendToFile(string $filename, $content)
    {
    }
    private function toIterable(string|iterable $files) : iterable
    {
    }
    private function getSchemeAndHierarchy(string $filename) : array
    {
    }
    private static function assertFunctionExists(string $func) : void
    {
    }
    private static function box(string $func, mixed ...$args) : mixed
    {
    }
}
namespace Symfony\Component\Filesystem;

/**
 * Contains utility methods for handling path strings.
 *
 * The methods in this class are able to deal with both UNIX and Windows paths
 * with both forward and backward slashes. All methods return normalized parts
 * containing only forward slashes and no excess "." and ".." segments.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Thomas Schulz <mail@king2500.net>
 * @author Théo Fidry <theo.fidry@gmail.com>
 */
final class Path
{
    private const CLEANUP_THRESHOLD = 1250;
    private const CLEANUP_SIZE = 1000;
    /**
     * Buffers input/output of {@link canonicalize()}.
     *
     * @var array<string, string>
     */
    private static $buffer = [];
    /**
     * @var int
     */
    private static $bufferSize = 0;
    public static function canonicalize(string $path) : string
    {
    }
    /**
     * Normalizes the given path.
     *
     * During normalization, all slashes are replaced by forward slashes ("/").
     * Contrary to {@link canonicalize()}, this method does not remove invalid
     * or dot path segments. Consequently, it is much more efficient and should
     * be used whenever the given path is known to be a valid, absolute system
     * path.
     *
     * This method is able to deal with both UNIX and Windows paths.
     */
    public static function normalize(string $path) : string
    {
    }
    /**
     * Returns the directory part of the path.
     *
     * This method is similar to PHP's dirname(), but handles various cases
     * where dirname() returns a weird result:
     *
     *  - dirname() does not accept backslashes on UNIX
     *  - dirname("C:/symfony") returns "C:", not "C:/"
     *  - dirname("C:/") returns ".", not "C:/"
     *  - dirname("C:") returns ".", not "C:/"
     *  - dirname("symfony") returns ".", not ""
     *  - dirname() does not canonicalize the result
     *
     * This method fixes these shortcomings and behaves like dirname()
     * otherwise.
     *
     * The result is a canonical path.
     *
     * @return string The canonical directory part. Returns the root directory
     *                if the root directory is passed. Returns an empty string
     *                if a relative path is passed that contains no slashes.
     *                Returns an empty string if an empty string is passed.
     */
    public static function getDirectory(string $path) : string
    {
    }
    /**
     * Returns canonical path of the user's home directory.
     *
     * Supported operating systems:
     *
     *  - UNIX
     *  - Windows8 and upper
     *
     * If your operating system or environment isn't supported, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @throws RuntimeException If your operating system or environment isn't supported
     */
    public static function getHomeDirectory() : string
    {
    }
    /**
     * Returns the root directory of a path.
     *
     * The result is a canonical path.
     *
     * @return string The canonical root directory. Returns an empty string if
     *                the given path is relative or empty.
     */
    public static function getRoot(string $path) : string
    {
    }
    /**
     * Returns the file name without the extension from a file path.
     *
     * @param string|null $extension if specified, only that extension is cut
     *                               off (may contain leading dot)
     */
    public static function getFilenameWithoutExtension(string $path, string $extension = null) : string
    {
    }
    /**
     * Returns the extension from a file path (without leading dot).
     *
     * @param bool $forceLowerCase forces the extension to be lower-case
     */
    public static function getExtension(string $path, bool $forceLowerCase = false) : string
    {
    }
    /**
     * Returns whether the path has an (or the specified) extension.
     *
     * @param string               $path       the path string
     * @param string|string[]|null $extensions if null or not provided, checks if
     *                                         an extension exists, otherwise
     *                                         checks for the specified extension
     *                                         or array of extensions (with or
     *                                         without leading dot)
     * @param bool                 $ignoreCase whether to ignore case-sensitivity
     */
    public static function hasExtension(string $path, $extensions = null, bool $ignoreCase = false) : bool
    {
    }
    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change.
     * @param string $extension new extension (with or without leading dot)
     *
     * @return string the path string with new file extension
     */
    public static function changeExtension(string $path, string $extension) : string
    {
    }
    public static function isAbsolute(string $path) : bool
    {
    }
    public static function isRelative(string $path) : bool
    {
    }
    /**
     * Turns a relative path into an absolute path in canonical form.
     *
     * Usually, the relative path is appended to the given base path. Dot
     * segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * echo Path::makeAbsolute("../style.css", "/symfony/puli/css");
     * // => /symfony/puli/style.css
     * ```
     *
     * If an absolute path is passed, that path is returned unless its root
     * directory is different than the one of the base path. In that case, an
     * exception is thrown.
     *
     * ```php
     * Path::makeAbsolute("/style.css", "/symfony/puli/css");
     * // => /style.css
     *
     * Path::makeAbsolute("C:/style.css", "C:/symfony/puli/css");
     * // => C:/style.css
     *
     * Path::makeAbsolute("C:/style.css", "/symfony/puli/css");
     * // InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @param string $basePath an absolute base path
     *
     * @throws InvalidArgumentException if the base path is not absolute or if
     *                                  the given path is an absolute path with
     *                                  a different root than the base path
     */
    public static function makeAbsolute(string $path, string $basePath) : string
    {
    }
    /**
     * Turns a path into a relative path.
     *
     * The relative path is created relative to the given base path:
     *
     * ```php
     * echo Path::makeRelative("/symfony/style.css", "/symfony/puli");
     * // => ../style.css
     * ```
     *
     * If a relative path is passed and the base path is absolute, the relative
     * path is returned unchanged:
     *
     * ```php
     * Path::makeRelative("style.css", "/symfony/puli/css");
     * // => style.css
     * ```
     *
     * If both paths are relative, the relative path is created with the
     * assumption that both paths are relative to the same directory:
     *
     * ```php
     * Path::makeRelative("style.css", "symfony/puli/css");
     * // => ../../../style.css
     * ```
     *
     * If both paths are absolute, their root directory must be the same,
     * otherwise an exception is thrown:
     *
     * ```php
     * Path::makeRelative("C:/symfony/style.css", "/symfony/puli");
     * // InvalidArgumentException
     * ```
     *
     * If the passed path is absolute, but the base path is not, an exception
     * is thrown as well:
     *
     * ```php
     * Path::makeRelative("/symfony/style.css", "symfony/puli");
     * // InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @throws InvalidArgumentException if the base path is not absolute or if
     *                                  the given path has a different root
     *                                  than the base path
     */
    public static function makeRelative(string $path, string $basePath) : string
    {
    }
    public static function isLocal(string $path) : bool
    {
    }
    public static function getLongestCommonBasePath(string ...$paths) : ?string
    {
    }
    public static function join(string ...$paths) : string
    {
    }
    public static function isBasePath(string $basePath, string $ofPath) : bool
    {
    }
    /**
     * @return string[]
     */
    private static function findCanonicalParts(string $root, string $pathWithoutRoot) : array
    {
    }
    /**
     * Splits a canonical path into its root directory and the remainder.
     *
     * If the path has no root directory, an empty root directory will be
     * returned.
     *
     * If the root directory is a Windows style partition, the resulting root
     * will always contain a trailing slash.
     *
     * list ($root, $path) = Path::split("C:/symfony")
     * // => ["C:/", "symfony"]
     *
     * list ($root, $path) = Path::split("C:")
     * // => ["C:/", ""]
     *
     * @return array{string, string} an array with the root directory and the remaining relative path
     */
    private static function split(string $path) : array
    {
    }
    private static function toLower(string $string) : string
    {
    }
    private function __construct()
    {
    }
}
namespace Symfony\Component\Finder;

/**
 * Finder allows to build rules to find files and directories.
 *
 * It is a thin wrapper around several specialized iterator classes.
 *
 * All rules may be invoked several times.
 *
 * All methods return the current Finder object to allow chaining:
 *
 *     $finder = Finder::create()->files()->name('*.php')->in(__DIR__);
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @implements \IteratorAggregate<string, SplFileInfo>
 */
class Finder implements \IteratorAggregate, \Countable
{
    public const IGNORE_VCS_FILES = 1;
    public const IGNORE_DOT_FILES = 2;
    public const IGNORE_VCS_IGNORED_FILES = 4;
    private int $mode = 0;
    private array $names = [];
    private array $notNames = [];
    private array $exclude = [];
    private array $filters = [];
    private array $depths = [];
    private array $sizes = [];
    private bool $followLinks = false;
    private bool $reverseSorting = false;
    private \Closure|int|false $sort = false;
    private int $ignore = 0;
    private array $dirs = [];
    private array $dates = [];
    private array $iterators = [];
    private array $contains = [];
    private array $notContains = [];
    private array $paths = [];
    private array $notPaths = [];
    private bool $ignoreUnreadableDirs = false;
    private static array $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];
    public function __construct()
    {
    }
    public static function create() : static
    {
    }
    /**
     * Restricts the matching to directories only.
     *
     * @return $this
     */
    public function directories() : static
    {
    }
    /**
     * Restricts the matching to files only.
     *
     * @return $this
     */
    public function files() : static
    {
    }
    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *     $finder->depth('> 1') // the Finder will start matching at level 1.
     *     $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *     $finder->depth(['>= 1', '< 3'])
     *
     * @param string|int|string[]|int[] $levels The depth level expression or an array of depth levels
     *
     * @return $this
     *
     * @see DepthRangeFilterIterator
     * @see NumberComparator
     */
    public function depth(string|int|array $levels) : static
    {
    }
    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     *     $finder->date('since yesterday');
     *     $finder->date('until 2 days ago');
     *     $finder->date('> now - 2 hours');
     *     $finder->date('>= 2005-10-15');
     *     $finder->date(['>= 2005-10-15', '<= 2006-05-27']);
     *
     * @param string|string[] $dates A date range string or an array of date ranges
     *
     * @return $this
     *
     * @see strtotime
     * @see DateRangeFilterIterator
     * @see DateComparator
     */
    public function date(string|array $dates) : static
    {
    }
    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     *     $finder->name('/\.php$/')
     *     $finder->name('*.php') // same as above, without dot files
     *     $finder->name('test.php')
     *     $finder->name(['test.py', 'test.php'])
     *
     * @param string|string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     *
     * @return $this
     *
     * @see FilenameFilterIterator
     */
    public function name(string|array $patterns) : static
    {
    }
    /**
     * Adds rules that files must not match.
     *
     * @param string|string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     *
     * @return $this
     *
     * @see FilenameFilterIterator
     */
    public function notName(string|array $patterns) : static
    {
    }
    /**
     * Adds tests that file contents must match.
     *
     * Strings or PCRE patterns can be used:
     *
     *     $finder->contains('Lorem ipsum')
     *     $finder->contains('/Lorem ipsum/i')
     *     $finder->contains(['dolor', '/ipsum/i'])
     *
     * @param string|string[] $patterns A pattern (string or regexp) or an array of patterns
     *
     * @return $this
     *
     * @see FilecontentFilterIterator
     */
    public function contains(string|array $patterns) : static
    {
    }
    /**
     * Adds tests that file contents must not match.
     *
     * Strings or PCRE patterns can be used:
     *
     *     $finder->notContains('Lorem ipsum')
     *     $finder->notContains('/Lorem ipsum/i')
     *     $finder->notContains(['lorem', '/dolor/i'])
     *
     * @param string|string[] $patterns A pattern (string or regexp) or an array of patterns
     *
     * @return $this
     *
     * @see FilecontentFilterIterator
     */
    public function notContains(string|array $patterns) : static
    {
    }
    /**
     * Adds rules that filenames must match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     *     $finder->path('some/special/dir')
     *     $finder->path('/some\/special\/dir/') // same as above
     *     $finder->path(['some dir', 'another/dir'])
     *
     * Use only / as dirname separator.
     *
     * @param string|string[] $patterns A pattern (a regexp or a string) or an array of patterns
     *
     * @return $this
     *
     * @see FilenameFilterIterator
     */
    public function path(string|array $patterns) : static
    {
    }
    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     *     $finder->notPath('some/special/dir')
     *     $finder->notPath('/some\/special\/dir/') // same as above
     *     $finder->notPath(['some/file.txt', 'another/file.log'])
     *
     * Use only / as dirname separator.
     *
     * @param string|string[] $patterns A pattern (a regexp or a string) or an array of patterns
     *
     * @return $this
     *
     * @see FilenameFilterIterator
     */
    public function notPath(string|array $patterns) : static
    {
    }
    /**
     * Adds tests for file sizes.
     *
     *     $finder->size('> 10K');
     *     $finder->size('<= 1Ki');
     *     $finder->size(4);
     *     $finder->size(['> 10K', '< 20K'])
     *
     * @param string|int|string[]|int[] $sizes A size range string or an integer or an array of size ranges
     *
     * @return $this
     *
     * @see SizeRangeFilterIterator
     * @see NumberComparator
     */
    public function size(string|int|array $sizes) : static
    {
    }
    /**
     * Excludes directories.
     *
     * Directories passed as argument must be relative to the ones defined with the `in()` method. For example:
     *
     *     $finder->in(__DIR__)->exclude('ruby');
     *
     * @param string|array $dirs A directory path or an array of directories
     *
     * @return $this
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function exclude(string|array $dirs) : static
    {
    }
    /**
     * Excludes "hidden" directories and files (starting with a dot).
     *
     * This option is enabled by default.
     *
     * @return $this
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function ignoreDotFiles(bool $ignoreDotFiles) : static
    {
    }
    /**
     * Forces the finder to ignore version control directories.
     *
     * This option is enabled by default.
     *
     * @return $this
     *
     * @see ExcludeDirectoryFilterIterator
     */
    public function ignoreVCS(bool $ignoreVCS) : static
    {
    }
    /**
     * Forces Finder to obey .gitignore and ignore files based on rules listed there.
     *
     * This option is disabled by default.
     *
     * @return $this
     */
    public function ignoreVCSIgnored(bool $ignoreVCSIgnored) : static
    {
    }
    /**
     * Adds VCS patterns.
     *
     * @see ignoreVCS()
     *
     * @param string|string[] $pattern VCS patterns to ignore
     *
     * @return void
     */
    public static function addVCSPattern(string|array $pattern)
    {
    }
    /**
     * Sorts files and directories by an anonymous function.
     *
     * The anonymous function receives two \SplFileInfo instances to compare.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sort(\Closure $closure) : static
    {
    }
    /**
     * Sorts files and directories by extension.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByExtension() : static
    {
    }
    /**
     * Sorts files and directories by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByName(bool $useNaturalSort = false) : static
    {
    }
    /**
     * Sorts files and directories by name case insensitive.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByCaseInsensitiveName(bool $useNaturalSort = false) : static
    {
    }
    /**
     * Sorts files and directories by size.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortBySize() : static
    {
    }
    /**
     * Sorts files and directories by type (directories before files), then by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByType() : static
    {
    }
    /**
     * Sorts files and directories by the last accessed time.
     *
     * This is the time that the file was last accessed, read or written to.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByAccessedTime() : static
    {
    }
    /**
     * Reverses the sorting.
     *
     * @return $this
     */
    public function reverseSorting() : static
    {
    }
    /**
     * Sorts files and directories by the last inode changed time.
     *
     * This is the time that the inode information was last modified (permissions, owner, group or other metadata).
     *
     * On Windows, since inode is not available, changed time is actually the file creation time.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByChangedTime() : static
    {
    }
    /**
     * Sorts files and directories by the last modified time.
     *
     * This is the last time the actual contents of the file were last modified.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this
     *
     * @see SortableIterator
     */
    public function sortByModifiedTime() : static
    {
    }
    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     * @return $this
     *
     * @see CustomFilterIterator
     */
    public function filter(\Closure $closure) : static
    {
    }
    /**
     * Forces the following of symlinks.
     *
     * @return $this
     */
    public function followLinks() : static
    {
    }
    /**
     * Tells finder to ignore unreadable directories.
     *
     * By default, scanning unreadable directories content throws an AccessDeniedException.
     *
     * @return $this
     */
    public function ignoreUnreadableDirs(bool $ignore = true) : static
    {
    }
    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|string[] $dirs A directory path or an array of directories
     *
     * @return $this
     *
     * @throws DirectoryNotFoundException if one of the directories does not exist
     */
    public function in(string|array $dirs) : static
    {
    }
    /**
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return \Iterator<string, SplFileInfo>
     *
     * @throws \LogicException if the in() method has not been called
     */
    public function getIterator() : \Iterator
    {
    }
    /**
     * Appends an existing set of files/directories to the finder.
     *
     * The set can be another Finder, an Iterator, an IteratorAggregate, or even a plain array.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException when the given argument is not iterable
     */
    public function append(iterable $iterator) : static
    {
    }
    public function hasResults() : bool
    {
    }
    public function count() : int
    {
    }
    private function searchInDirectory(string $dir) : \Iterator
    {
    }
    private function normalizeDir(string $dir) : string
    {
    }
}
namespace Symfony\Component\Process\Exception;

/**
 * Marker Interface for the Process Component.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ExceptionInterface extends \Throwable
{
}
namespace Symfony\Component\Process;

/**
 * Generic executable finder.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ExecutableFinder
{
    private $suffixes = ['.exe', '.bat', '.cmd', '.com'];
    /**
     * Replaces default suffixes of executable.
     *
     * @return void
     */
    public function setSuffixes(array $suffixes)
    {
    }
    /**
     * Adds new possible suffix to check for executable.
     *
     * @return void
     */
    public function addSuffix(string $suffix)
    {
    }
    /**
     * Finds an executable by name.
     *
     * @param string      $name      The executable name (without the extension)
     * @param string|null $default   The default to return if no executable is found
     * @param array       $extraDirs Additional dirs to check into
     */
    public function find(string $name, string $default = null, array $extraDirs = []) : ?string
    {
    }
}
namespace Symfony\Component\Process;

/**
 * Process is a thin wrapper around proc_* functions to easily
 * start independent PHP processes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @implements \IteratorAggregate<string, string>
 */
class Process implements \IteratorAggregate
{
    public const ERR = 'err';
    public const OUT = 'out';
    public const STATUS_READY = 'ready';
    public const STATUS_STARTED = 'started';
    public const STATUS_TERMINATED = 'terminated';
    public const STDIN = 0;
    public const STDOUT = 1;
    public const STDERR = 2;
    public const TIMEOUT_PRECISION = 0.2;
    public const ITER_NON_BLOCKING = 1;
    public const ITER_KEEP_OUTPUT = 2;
    public const ITER_SKIP_OUT = 4;
    public const ITER_SKIP_ERR = 8;
    private $callback;
    private $hasCallback = false;
    private $commandline;
    private $cwd;
    private $env = [];
    private $input;
    private $starttime;
    private $lastOutputTime;
    private $timeout;
    private $idleTimeout;
    private $exitcode;
    private $fallbackStatus = [];
    private $processInformation;
    private $outputDisabled = false;
    private $stdout;
    private $stderr;
    private $process;
    private $status = self::STATUS_READY;
    private $incrementalOutputOffset = 0;
    private $incrementalErrorOutputOffset = 0;
    private $tty = false;
    private $pty;
    private $options = ['suppress_errors' => true, 'bypass_shell' => true];
    private $useFileHandles = false;
    /** @var PipesInterface */
    private $processPipes;
    private $latestSignal;
    private static $sigchild;
    public static $exitCodes = [0 => 'OK', 1 => 'General error', 2 => 'Misuse of shell builtins', 126 => 'Invoked command cannot execute', 127 => 'Command not found', 128 => 'Invalid exit argument', 129 => 'Hangup', 130 => 'Interrupt', 131 => 'Quit and dump core', 132 => 'Illegal instruction', 133 => 'Trace/breakpoint trap', 134 => 'Process aborted', 135 => 'Bus error: "access to undefined portion of memory object"', 136 => 'Floating point exception: "erroneous arithmetic operation"', 137 => 'Kill (terminate immediately)', 138 => 'User-defined 1', 139 => 'Segmentation violation', 140 => 'User-defined 2', 141 => 'Write to pipe with no one reading', 142 => 'Signal raised by alarm', 143 => 'Termination (request to terminate)', 145 => 'Child process terminated, stopped (or continued*)', 146 => 'Continue if stopped', 147 => 'Stop executing temporarily', 148 => 'Terminal stop signal', 149 => 'Background process attempting to read from tty ("in")', 150 => 'Background process attempting to write to tty ("out")', 151 => 'Urgent data available on socket', 152 => 'CPU time limit exceeded', 153 => 'File size limit exceeded', 154 => 'Signal raised by timer counting virtual time: "virtual timer expired"', 155 => 'Profiling timer expired', 157 => 'Pollable event', 159 => 'Bad syscall'];
    /**
     * @param array          $command The command to run and its arguments listed as separate entries
     * @param string|null    $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env     The environment variables or null to use the same environment as the current PHP process
     * @param mixed          $input   The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout The timeout in seconds or null to disable
     *
     * @throws LogicException When proc_open is not installed
     */
    public function __construct(array $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60)
    {
    }
    /**
     * Creates a Process instance as a command-line to be run in a shell wrapper.
     *
     * Command-lines are parsed by the shell of your OS (/bin/sh on Unix-like, cmd.exe on Windows.)
     * This allows using e.g. pipes or conditional execution. In this mode, signals are sent to the
     * shell wrapper and not to your commands.
     *
     * In order to inject dynamic values into command-lines, we strongly recommend using placeholders.
     * This will save escaping values, which is not portable nor secure anyway:
     *
     *   $process = Process::fromShellCommandline('my_command "${:MY_VAR}"');
     *   $process->run(null, ['MY_VAR' => $theValue]);
     *
     * @param string         $command The command line to pass to the shell of the OS
     * @param string|null    $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env     The environment variables or null to use the same environment as the current PHP process
     * @param mixed          $input   The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout The timeout in seconds or null to disable
     *
     * @throws LogicException When proc_open is not installed
     */
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, mixed $input = null, ?float $timeout = 60) : static
    {
    }
    public function __sleep() : array
    {
    }
    public function __wakeup()
    {
    }
    public function __destruct()
    {
    }
    public function __clone()
    {
    }
    /**
     * Runs the process.
     *
     * The callback receives the type of output (out or err) and
     * some bytes from the output in real-time. It allows to have feedback
     * from the independent process during execution.
     *
     * The STDOUT and STDERR are also available after the process is finished
     * via the getOutput() and getErrorOutput() methods.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                output available on STDOUT or STDERR
     *
     * @return int The exit status code
     *
     * @throws RuntimeException         When process can't be launched
     * @throws RuntimeException         When process is already running
     * @throws ProcessTimedOutException When process timed out
     * @throws ProcessSignaledException When process stopped after receiving signal
     * @throws LogicException           In case a callback is provided and output has been disabled
     *
     * @final
     */
    public function run(callable $callback = null, array $env = []) : int
    {
    }
    /**
     * Runs the process.
     *
     * This is identical to run() except that an exception is thrown if the process
     * exits with a non-zero exit code.
     *
     * @return $this
     *
     * @throws ProcessFailedException if the process didn't terminate successfully
     *
     * @final
     */
    public function mustRun(callable $callback = null, array $env = []) : static
    {
    }
    /**
     * Starts the process and returns after writing the input to STDIN.
     *
     * This method blocks until all STDIN data is sent to the process then it
     * returns while the process runs in the background.
     *
     * The termination of the process can be awaited with wait().
     *
     * The callback receives the type of output (out or err) and some bytes from
     * the output in real-time while writing the standard input to the process.
     * It allows to have feedback from the independent process during execution.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                output available on STDOUT or STDERR
     *
     * @return void
     *
     * @throws RuntimeException When process can't be launched
     * @throws RuntimeException When process is already running
     * @throws LogicException   In case a callback is provided and output has been disabled
     */
    public function start(callable $callback = null, array $env = [])
    {
    }
    /**
     * Restarts the process.
     *
     * Be warned that the process is cloned before being started.
     *
     * @param callable|null $callback A PHP callback to run whenever there is some
     *                                output available on STDOUT or STDERR
     *
     * @throws RuntimeException When process can't be launched
     * @throws RuntimeException When process is already running
     *
     * @see start()
     *
     * @final
     */
    public function restart(callable $callback = null, array $env = []) : static
    {
    }
    /**
     * Waits for the process to terminate.
     *
     * The callback receives the type of output (out or err) and some bytes
     * from the output in real-time while writing the standard input to the process.
     * It allows to have feedback from the independent process during execution.
     *
     * @param callable|null $callback A valid PHP callback
     *
     * @return int The exitcode of the process
     *
     * @throws ProcessTimedOutException When process timed out
     * @throws ProcessSignaledException When process stopped after receiving signal
     * @throws LogicException           When process is not yet started
     */
    public function wait(callable $callback = null) : int
    {
    }
    /**
     * Waits until the callback returns true.
     *
     * The callback receives the type of output (out or err) and some bytes
     * from the output in real-time while writing the standard input to the process.
     * It allows to have feedback from the independent process during execution.
     *
     * @throws RuntimeException         When process timed out
     * @throws LogicException           When process is not yet started
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function waitUntil(callable $callback) : bool
    {
    }
    /**
     * Returns the Pid (process identifier), if applicable.
     *
     * @return int|null The process id if running, null otherwise
     */
    public function getPid() : ?int
    {
    }
    /**
     * Sends a POSIX signal to the process.
     *
     * @param int $signal A valid POSIX signal (see https://php.net/pcntl.constants)
     *
     * @return $this
     *
     * @throws LogicException   In case the process is not running
     * @throws RuntimeException In case --enable-sigchild is activated and the process can't be killed
     * @throws RuntimeException In case of failure
     */
    public function signal(int $signal) : static
    {
    }
    /**
     * Disables fetching output and error output from the underlying process.
     *
     * @return $this
     *
     * @throws RuntimeException In case the process is already running
     * @throws LogicException   if an idle timeout is set
     */
    public function disableOutput() : static
    {
    }
    /**
     * Enables fetching output and error output from the underlying process.
     *
     * @return $this
     *
     * @throws RuntimeException In case the process is already running
     */
    public function enableOutput() : static
    {
    }
    public function isOutputDisabled() : bool
    {
    }
    /**
     * Returns the current output of the process (STDOUT).
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getOutput() : string
    {
    }
    /**
     * Returns the output incrementally.
     *
     * In comparison with the getOutput method which always return the whole
     * output, this one returns the new output since the last call.
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getIncrementalOutput() : string
    {
    }
    /**
     * Returns an iterator to the output of the process, with the output type as keys (Process::OUT/ERR).
     *
     * @param int $flags A bit field of Process::ITER_* flags
     *
     * @return \Generator<string, string>
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getIterator(int $flags = 0) : \Generator
    {
    }
    /**
     * Clears the process output.
     *
     * @return $this
     */
    public function clearOutput() : static
    {
    }
    /**
     * Returns the current error output of the process (STDERR).
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getErrorOutput() : string
    {
    }
    /**
     * Returns the errorOutput incrementally.
     *
     * In comparison with the getErrorOutput method which always return the
     * whole error output, this one returns the new error output since the last
     * call.
     *
     * @throws LogicException in case the output has been disabled
     * @throws LogicException In case the process is not started
     */
    public function getIncrementalErrorOutput() : string
    {
    }
    /**
     * Clears the process output.
     *
     * @return $this
     */
    public function clearErrorOutput() : static
    {
    }
    /**
     * Returns the exit code returned by the process.
     *
     * @return int|null The exit status code, null if the Process is not terminated
     */
    public function getExitCode() : ?int
    {
    }
    /**
     * Returns a string representation for the exit code returned by the process.
     *
     * This method relies on the Unix exit code status standardization
     * and might not be relevant for other operating systems.
     *
     * @return string|null A string representation for the exit status code, null if the Process is not terminated
     *
     * @see http://tldp.org/LDP/abs/html/exitcodes.html
     * @see http://en.wikipedia.org/wiki/Unix_signal
     */
    public function getExitCodeText() : ?string
    {
    }
    public function isSuccessful() : bool
    {
    }
    /**
     * Returns true if the child process has been terminated by an uncaught signal.
     *
     * It always returns false on Windows.
     *
     * @throws LogicException In case the process is not terminated
     */
    public function hasBeenSignaled() : bool
    {
    }
    /**
     * Returns the number of the signal that caused the child process to terminate its execution.
     *
     * It is only meaningful if hasBeenSignaled() returns true.
     *
     * @throws RuntimeException In case --enable-sigchild is activated
     * @throws LogicException   In case the process is not terminated
     */
    public function getTermSignal() : int
    {
    }
    /**
     * Returns true if the child process has been stopped by a signal.
     *
     * It always returns false on Windows.
     *
     * @throws LogicException In case the process is not terminated
     */
    public function hasBeenStopped() : bool
    {
    }
    /**
     * Returns the number of the signal that caused the child process to stop its execution.
     *
     * It is only meaningful if hasBeenStopped() returns true.
     *
     * @throws LogicException In case the process is not terminated
     */
    public function getStopSignal() : int
    {
    }
    public function isRunning() : bool
    {
    }
    public function isStarted() : bool
    {
    }
    public function isTerminated() : bool
    {
    }
    public function getStatus() : string
    {
    }
    /**
     * Stops the process.
     *
     * @param int|float $timeout The timeout in seconds
     * @param int|null  $signal  A POSIX signal to send in case the process has not stop at timeout, default is SIGKILL (9)
     *
     * @return int|null The exit-code of the process or null if it's not running
     */
    public function stop(float $timeout = 10, int $signal = null) : ?int
    {
    }
    public function getLastOutputTime() : ?float
    {
    }
    public function getCommandLine() : string
    {
    }
    public function getTimeout() : ?float
    {
    }
    public function getIdleTimeout() : ?float
    {
    }
    /**
     * Sets the process timeout (max. runtime) in seconds.
     *
     * To disable the timeout, set this value to null.
     *
     * @return $this
     *
     * @throws InvalidArgumentException if the timeout is negative
     */
    public function setTimeout(?float $timeout) : static
    {
    }
    /**
     * Sets the process idle timeout (max. time since last output) in seconds.
     *
     * To disable the timeout, set this value to null.
     *
     * @return $this
     *
     * @throws LogicException           if the output is disabled
     * @throws InvalidArgumentException if the timeout is negative
     */
    public function setIdleTimeout(?float $timeout) : static
    {
    }
    /**
     * Enables or disables the TTY mode.
     *
     * @return $this
     *
     * @throws RuntimeException In case the TTY mode is not supported
     */
    public function setTty(bool $tty) : static
    {
    }
    public function isTty() : bool
    {
    }
    /**
     * Sets PTY mode.
     *
     * @return $this
     */
    public function setPty(bool $bool) : static
    {
    }
    public function isPty() : bool
    {
    }
    public function getWorkingDirectory() : ?string
    {
    }
    /**
     * Sets the current working directory.
     *
     * @return $this
     */
    public function setWorkingDirectory(string $cwd) : static
    {
    }
    public function getEnv() : array
    {
    }
    /**
     * Sets the environment variables.
     *
     * @param array<string|\Stringable> $env The new environment variables
     *
     * @return $this
     */
    public function setEnv(array $env) : static
    {
    }
    /**
     * Gets the Process input.
     *
     * @return resource|string|\Iterator|null
     */
    public function getInput()
    {
    }
    /**
     * Sets the input.
     *
     * This content will be passed to the underlying process standard input.
     *
     * @param string|int|float|bool|resource|\Traversable|null $input The content
     *
     * @return $this
     *
     * @throws LogicException In case the process is running
     */
    public function setInput(mixed $input) : static
    {
    }
    /**
     * Performs a check between the timeout definition and the time the process started.
     *
     * In case you run a background process (with the start method), you should
     * trigger this method regularly to ensure the process timeout
     *
     * @return void
     *
     * @throws ProcessTimedOutException In case the timeout was reached
     */
    public function checkTimeout()
    {
    }
    /**
     * @throws LogicException in case process is not started
     */
    public function getStartTime() : float
    {
    }
    /**
     * Defines options to pass to the underlying proc_open().
     *
     * @see https://php.net/proc_open for the options supported by PHP.
     *
     * Enabling the "create_new_console" option allows a subprocess to continue
     * to run after the main process exited, on both Windows and *nix
     *
     * @return void
     */
    public function setOptions(array $options)
    {
    }
    public static function isTtySupported() : bool
    {
    }
    public static function isPtySupported() : bool
    {
    }
    private function getDescriptors() : array
    {
    }
    /**
     * Builds up the callback used by wait().
     *
     * The callbacks adds all occurred output to the specific buffer and calls
     * the user callback (if present) with the received output.
     *
     * @param callable|null $callback The user defined PHP callback
     */
    protected function buildCallback(callable $callback = null) : \Closure
    {
    }
    /**
     * Updates the status of the process, reads pipes.
     *
     * @param bool $blocking Whether to use a blocking read call
     *
     * @return void
     */
    protected function updateStatus(bool $blocking)
    {
    }
    protected function isSigchildEnabled() : bool
    {
    }
    /**
     * Reads pipes for the freshest output.
     *
     * @param string $caller   The name of the method that needs fresh outputs
     * @param bool   $blocking Whether to use blocking calls or not
     *
     * @throws LogicException in case output has been disabled or process is not started
     */
    private function readPipesForOutput(string $caller, bool $blocking = false) : void
    {
    }
    /**
     * Validates and returns the filtered timeout.
     *
     * @throws InvalidArgumentException if the given timeout is a negative number
     */
    private function validateTimeout(?float $timeout) : ?float
    {
    }
    /**
     * Reads pipes, executes callback.
     *
     * @param bool $blocking Whether to use blocking calls or not
     * @param bool $close    Whether to close file handles or not
     */
    private function readPipes(bool $blocking, bool $close) : void
    {
    }
    /**
     * Closes process resource, closes file handles, sets the exitcode.
     *
     * @return int The exitcode
     */
    private function close() : int
    {
    }
    private function resetProcessData() : void
    {
    }
    /**
     * Sends a POSIX signal to the process.
     *
     * @param int  $signal         A valid POSIX signal (see https://php.net/pcntl.constants)
     * @param bool $throwException Whether to throw exception in case signal failed
     *
     * @throws LogicException   In case the process is not running
     * @throws RuntimeException In case --enable-sigchild is activated and the process can't be killed
     * @throws RuntimeException In case of failure
     */
    private function doSignal(int $signal, bool $throwException) : bool
    {
    }
    private function prepareWindowsCommandLine(string $cmd, array &$env) : string
    {
    }
    /**
     * Ensures the process is running or terminated, throws a LogicException if the process has a not started.
     *
     * @throws LogicException if the process has not run
     */
    private function requireProcessIsStarted(string $functionName) : void
    {
    }
    /**
     * Ensures the process is terminated, throws a LogicException if the process has a status different than "terminated".
     *
     * @throws LogicException if the process is not yet terminated
     */
    private function requireProcessIsTerminated(string $functionName) : void
    {
    }
    private function escapeArgument(?string $argument) : string
    {
    }
    private function replacePlaceholders(string $commandline, array $env) : string
    {
    }
    private function getDefaultEnv() : array
    {
    }
}