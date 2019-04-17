<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', function (Request $request) {
    return response()->json(['data' => "Hello World", 'message' => 'Hello World'], 400);
});

//Role list with pagination
Route::get('/role/list/search', 'PageController@RoleListViaPagination');
//Role list without pagination
Route::get('/role/list', 'PageController@RoleList');
//Role list count
Route::get('/role/count', 'PageController@RoleCount');


//user list with pagination
Route::get('/user/list', 'UserController@UserListViaPagination');
//user list without pagination
Route::get('/user/list/not/useable', 'UserController@UserList');
//user list count
Route::get('/user/list/count', 'UserController@UserCount');
//User update route
Route::post('/user/update/basic', 'UserController@UserUpdateBasic');
//Get single user via id
Route::get('/user/single', 'UserController@GetSingleUserViaId');
//User registration
Route::post('/user/add', 'UserController@UserRegistration');
//User registration
Route::post('/user/update/full/information', 'UserController@UpdateUserFullInformation');
//User delete route
Route::post('/user/delete', 'UserController@UserDelete');
//User invitation list with pagination and search
Route::get('/user/invitation', 'UserController@GetUserInvitationListWithPaginationAndSearch');
//User invitation list count with search
Route::get('/user/invitation/count', 'UserController@GetUserInvitationListCount');
//User block route
Route::post('/user/block', 'UserController@UserBlock');
//User unblock route
Route::post('/user/unblock', 'UserController@UserUnblock');

//get support staff list
Route::get('/user/via/role', 'UserController@GetUserViaRoleCode');

//User Change Password
Route::put('/user/change/password', 'UserController@UserChangePassword');


//Associate doctor to facilitator route
Route::post('/associate/doctor/facilitator', 'UserController@AssociateFacilitatorsWithDoctor');


//permission list with pagination
Route::get('/permission/list/search', 'PageController@PermissionListViaPagination');
//permission list without pagination
Route::get('/permission/list', 'PageController@PermissionList');
//permission list count
Route::get('/permission/count', 'PageController@PermissionCount');
//Role permission assign
Route::post('/role/permission/assign', 'PageController@RolePermissionAssign');
//Get permission via role Id
Route::get('/permission/via/role/id', 'UserController@PermissionViaRoleId');
//Get permission via user Id
Route::get('/permission/via/user/id', 'UserController@PermissionViaUserId');
//Test file upload
Route::post('/upload/file', 'DocumentUploadController@UploadFiles');

//Get doctor facilitator list
Route::get('/doctor/facilitator', 'UserController@GetAssociateFacilitator');


Route::get('/', function () {
    return 'Hello';
});

Route::get('/test/list', 'PageController@testFunction');

Route::get('/test/email', 'PageController@TestEmail');

Route::get('/test/sms', 'PageController@TestSms');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'LoginController@login');
Route::post('/register', 'LoginController@register');
Route::post('/forgetPass', 'LoginController@forgetPass');
Route::post('/resetPass', 'LoginController@resetPass');


//template list without pagination
Route::get('/template/list', 'TemplateController@TemplateList');

//template list without pagination
Route::get('/template/list/via/type', 'TemplateController@TemplateListViaType');

//template list count
Route::get('/template/list/count', 'TemplateController@TemplateCount');

//template list with pagination
Route::get('/template/list/search', 'TemplateController@TemplateListViaPagination');

//Get single template via id
Route::get('/template/single', 'TemplateController@GetSingleTemplateViaId');
//template add
Route::post('/template/add', 'TemplateController@TemplateAdd');
//template updata
Route::post('/template/update', 'TemplateController@TemplateUpdate');

//template delete
//Route::post('/template/update', 'TemplateController@TemplateDelete');  work not done


//proposal list without pagination
Route::get('/proposal/list', 'ProposalController@ProposalList');

//proposal list count
Route::get('/proposal/list/count', 'ProposalController@ProposalCount');

//proposal list with pagination
Route::get('/proposal/list/search', 'ProposalController@ProposalListViaPagination');

//Get single proposal via id
Route::get('/proposal/single', 'ProposalController@GetSingleProposalViaId');
//proposal add
Route::post('/proposal/add', 'ProposalController@ProposalAdd');
//proposal updata
Route::post('/proposal/update', 'ProposalController@ProposalUpdate');

//proposal delete
//Route::post('/template/update', 'TemplateController@TemplateDelete');  work not done

Route::get('/offer', 'ServicesController@offer');
//proposal type list
Route::get('/type/list', 'ServicesController@TypeList');
Route::get('/type/subscription/list', 'ServicesController@TypeSubscriptionList');


//$time = strtotime($dateInUTC.' UTC');
//$dateInLocal = date("Y-m-d H:i:s", $time);



