<?php

namespace Option;

use Option\Model\OptionProductQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class Option extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'Option';

    /** @var string  */
    const OPTION_CATEGORY_TITLE = 'option_category_thelia';

    /** @var string  */
    const OPTION_CATEGORY_ID = 'option_category_id_thelia';

    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con);
    
        try {
            OptionProductQuery::create()->findOne();
        } catch (\Exception $ex) {
            $database->insertSql(null, array(__DIR__ . '/Config/TheliaMain.sql'));
        }
    }

    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = (new Finder())
            ->files()
            ->name('#.*?\.sql#')
            ->sortByName()
            ->in(__DIR__ . DS . 'Config' . DS . 'update');

        $database = new Database($con);

        /** @var \Symfony\Component\Finder\SplFileInfo $updateSQLFile */
        foreach ($finder as $updateSQLFile) {
            if (version_compare($currentVersion, str_replace('.sql', '', $updateSQLFile->getFilename()), '<')) {
                $database->insertSql(
                    null,
                    [
                        $updateSQLFile->getPathname()
                    ]
                );
            }
        }
    }

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
