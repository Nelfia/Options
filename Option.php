<?php

namespace Option;

use Option\Model\OptionProductQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
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
