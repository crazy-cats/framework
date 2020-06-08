<?php

namespace CrazyCat\Framework\App\Setup;

use CrazyCat\Framework\App\Cache\Manager as CacheManager;
use CrazyCat\Framework\App\Config as AppConfig;
use CrazyCat\Framework\App\Data\DataObject;
use CrazyCat\Framework\App\Db\Manager as DbManager;
use CrazyCat\Framework\App\Io\Http\Session\Manager as SessionManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Config extends \Symfony\Component\Console\Command\Command
{
    const VALUE_SEPARATOR = '|||';
    const VALUE_UNDEFINED = '__SETTING_VALUE_UNDEFINED__';
    const VALUE_HIDE = '1';
    const VALUE_SHOW = '0';

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \CrazyCat\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $settings;

    public function __construct(
        \CrazyCat\Framework\App\ObjectManager $objectManager,
        string $name = null
    ) {
        $this->objectManager = $objectManager;
        $this->initSettings();

        parent::__construct($name);
    }

    /**
     * @return void
     */
    private function initSettings()
    {
        $this->settings = [
            'global'   => [
                CacheManager::CONFIG_KEY   => [
                    'type' => 'files'
                ],
                SessionManager::CONFIG_KEY => [
                    'type' => 'files'
                ],
                DbManager::CONFIG_KEY      => [
                    'default' => [
                        'type'     => 'mysql',
                        'host'     => self::VALUE_UNDEFINED,
                        'username' => self::VALUE_UNDEFINED,
                        'password' => self::VALUE_UNDEFINED . self::VALUE_SEPARATOR . self::VALUE_HIDE,
                        'database' => self::VALUE_UNDEFINED,
                        'prefix'   => self::VALUE_UNDEFINED . self::VALUE_SEPARATOR . self::VALUE_SHOW . self::VALUE_SEPARATOR . ''
                    ]
                ],
                'lang'                     => 'en_US',
                'production_mode'          => false
            ],
            'api'      => [
                'token' => md5(date('Y-m-d H:i:s') . uniqid())
            ],
            'backend'  => [
                'route'     => self::VALUE_UNDEFINED,
                'lang'      => 'en_US',
                'theme'     => 'default',
                'merge_css' => false,
                'cookies'   => [
                    'duration' => 3600
                ]
            ],
            'frontend' => [
                'lang'      => 'en_US',
                'theme'     => 'default',
                'merge_css' => false,
                'cookies'   => [
                    'duration' => 3600
                ]
            ]
        ];
    }

    /**
     * @param array|string $setting
     * @param string       $path
     * @throws \ReflectionException
     */
    private function getInputSettings(&$setting, $path = '')
    {
        if (is_array($setting)) {
            foreach ($setting as $field => &$childSettings) {
                $this->getInputSettings($childSettings, $path ? ($path . '/' . $field) : $field);
            }
        } elseif (strpos($setting, self::VALUE_UNDEFINED) === 0) {
            list(, $isHidden, $default) = array_pad(explode(self::VALUE_SEPARATOR, $setting), 3, null);
            $question = $this->objectManager->create(
                Question::class,
                [
                    'question' => sprintf('Please set %s: ', $path),
                    'default'  => $default
                ]
            );
            if ($isHidden == self::VALUE_HIDE) {
                $question->setHidden(true);
                $question->setHiddenFallback(false);
            }
            $helper = $this->getHelper('question');
            $setting = $helper->ask($this->input, $this->output, $question);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $output->writeln(
            "\n" .
            '<info>Follow the wizard to complete minimum configuration which will store at `app/config/env.php`.</info>' .
            "\n"
        );

        $this->getInputSettings($this->settings);
        file_put_contents(
            DIR_APP . DS . AppConfig::DIR . DS . AppConfig::FILE,
            sprintf("<?php\nreturn %s;\n", (new DataObject())->toString($this->settings))
        );

        return 0;
    }
}
