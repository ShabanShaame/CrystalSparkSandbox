<?php


namespace App\Services;


use App\Http\Controllers\CrystalCollectionController;
use App\Services\EnvService\BalanceService;

class SupernovaService
{

    public function getMainMenu(){

        $menuItem['name'] = 'Main Menu';
        $menuItem['function'] = 'getMainMenu';
        $menu[] = $menuItem ;

        $menuItem['name'] = 'balances';
        $menuItem['function'] = 'balanceMenu';
        $menu[] = $menuItem ;

        $menuItem['name'] = 'Crystal Collection';
        $menuItem['function'] = 'crystalcollectionMenu';
        $menu[] = $menuItem ;

        $menuItem['name'] = 'Jetski Menu';
        $menuItem['function'] = 'jetskiMenu';
        $menu[] = $menuItem ;

        return $menu ;



    }

    public function balanceMenu(){

        $menuItem['name'] = 'Balance Menu';
        $menuItem['function'] = 'balanceMenu';

        $menu[] = $menuItem ;

        $menuItem['name'] = 'Get Balance Builder Info';
        $menuItem['function'] = 'getBalanceBuilderData';
        $menu[] = $menuItem ;


        $menuItem['name'] = 'Reset Balance Builder';
        $menuItem['function'] = 'resetBalanceBuilder';
        $menu[] = $menuItem ;

        $menuItem['name'] = 'Rebuild balances';
        $menuItem['function'] = 'calculateBalances';
        $menu[] = $menuItem ;

        $menuItem['name'] = 'Display invalid transactions';
        $menuItem['function'] = 'displayInvalidTransaction';
        $menu[] = $menuItem ;

        return $menu ;

    }

    public function crystalCollectionMenu(){

        $menuItem['name'] = 'Crystal Collection Menu';
        $menuItem['function'] = 'crystalCollectionMenu';

        $menu[] = $menuItem ;

        $menuItem['name'] = 'Sync from google sheet';
        $menuItem['function'] = 'googlesheetSync';
        $menu[] = $menuItem ;

        return $menu ;

    }

    public function jetskiMenu(){

        $menuItem['name'] = 'Jetski';
        $menuItem['function'] = 'jetskiMenu';

        $menu[] = $menuItem ;

        $menuItem['name'] = 'Show canonizer jetski';
        $menuItem['function'] = 'canonizerJetskiView';
        $menu[] = $menuItem ;

        return $menu ;

    }

    public function canonizerJetskiView(){

        $jetskiService = new CanonizerJetski();

        $response = $this->jetskiMenu();
        $response[0]['lines'] = $jetskiService->viewActiveJetski();

        // dd($response);

        return $response ;

    }



    public function getBalanceBuilderData(){

        $balanceService = new BalanceService();

        $response = $this->balanceMenu();
        $response[0]['lines'] = $balanceService->getEventData();

       // dd($response);

        return $response ;

    }

    public function resetBalanceBuilder(){

        $balanceService = new BalanceService();

        $response = $this->balanceMenu();
        $response[0]['lines'] = $balanceService->clearBalances();

        // dd($response);

        return $response ;

    }

    public function calculateBalances(){

        ini_set('memory_limit','10G');

        $balanceService = new BalanceService();

        $response = $this->balanceMenu();
        $response[0]['lines'] = $balanceService->buildBalances();

        // dd($response);

        return $response ;

    }

    public function displayInvalidTransaction(){

        $balanceService = new BalanceService();

        $response = $this->balanceMenu();
        $response[0]['lines'] = $balanceService->displayInvalidTransactions();

        // dd($response);

        return $response ;

    }

    public function googlesheetSync(){

            $controller = new CrystalCollectionController();
            $response = $this->crystalCollectionMenu();
            $response[0]['lines'] = $controller->synchAssetsFromGoogleSheet();
          //  dd($response);
            return $response ;



    }


}
