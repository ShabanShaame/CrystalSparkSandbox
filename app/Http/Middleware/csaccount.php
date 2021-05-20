<?php

namespace App\Http\Middleware;

use App\ResponseService;
use App\Services\JWTService;
use Closure;
use CsAccount\CsAccountManager;
use CsAccount\CsAccountRole;
use CsAccount\CsAccountUser;

class csaccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (isset($_REQUEST['requestServerChallenge'])){

            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        }




        $adminAddress = '0x7f7EED1fcBb2C2cf64d055eED1Ee051DD649C8e7';
        $theau = '0xF68690FBa7319D98a2C580E7060e9fEFaF61eB27';
        $userAddress = '0x1E6638CA282D643e5c9a1651BC894A8e527D78b2';


        $anAdmin = new CsAccountRole('admin');
        $aUser = new CsAccountRole('user');
        $aGuest = new CsAccountRole('guest');



        $csAccountManager = CsAccountManager::initWithDebugServerKey()->withCookies();


        if(isset($_GET['loginBySign'])){
         return response()->json($csAccountManager->authenticate($_POST['signature'],$_POST['message'])) ;


        }
        $jwt = null ;
        if(isset($_GET['jwt'])){
            $jwt = $_GET['jwt'];

        }





        CsAccountUser::initWithName("belovedAdmin")
            ->setRoles([$anAdmin])
            ->setAuthEthSignAddresses([$adminAddress])
            ->addToManager($csAccountManager);

        CsAccountUser::initWithName("belovedAdmin")
            ->setRoles([$anAdmin])
            ->setAuthEthSignAddresses([$theau])
            ->addToManager($csAccountManager);

        CsAccountUser::initWithName("respectedMember")
            ->setRoles([$aUser])
            ->setAuthEthSignAddresses([$userAddress])
            ->addToManager($csAccountManager);

        if ($csAccountManager->hasRole(new CsAccountRole('admin'),$jwt)){
            //echo "admin";
        } else {
           return $this->failAccess($csAccountManager);
            //die("after auth");
        }





//        if ($csAccountManager->hasRole(new CsAccountRole('user'))){
//            //echo "user";
//        } else echo "not user";



        return $next($request);
    }

    private function failAccess(CsAccountManager $csAccountManager){

        if (!isset($_REQUEST['json'])) {
            $csAccountManager->renderAuthView();
        }

        $responeService = new ResponseService();
        $responeService->prepareError(401,"not logged");


        return response()->json($responeService->response,401);





    }
}
