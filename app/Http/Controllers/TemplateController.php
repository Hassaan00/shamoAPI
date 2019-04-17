<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use View;
use App\Models\UserModel;
use App\Models\TemplateModel;
use App\Models\GenericModel;
use App\Models\HelperModel;
use Config;
use Carbon\Carbon;

class TemplateController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    //template list for combo box
    function TemplateList()
    {
        $value = TemplateModel::getTemplateList();

        $resultArray = json_decode(json_encode($value), true);
//        $data = $resultArray;

        $data = array();

        foreach ($resultArray as $val){

            $templateDetails = array();

            $templateDetails ['Id'] = $val['Id'];
            $templateDetails ['Name'] = $val['Name'];
            $templateDetails ['Content'] = $val['Content'];
            $templateDetails ['HtmlContent'] = $val['HtmlContent'];
            $templateDetails ['IsActive'] = $val['IsActive'];
            $templateDetails ['ProposalType'] = array();
            $templateDetails ['ProposalType']['Id'] = $val['ProposalTypeId'];
            $templateDetails ['ProposalType']['Name'] = $val['ProposalTypeName'];
            $templateDetails ['ProposalType']['Code'] = $val['ProposalTypeCode'];


            array_push($data,$templateDetails);
        }



        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Users fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Users not found'], 200);
        }
    }

    //template list for combo box
    function TemplateListViaType(Request $request)
    {

        $userId = $request->input('userId');
        $typeId = $request->input('typeId');

        if(empty(trim($typeId))){
            return response()->json(['data' => null, 'message' => 'Type is required'], 200);
        }


        $value = TemplateModel::getTemplateListViaType($typeId);

        $resultArray = json_decode(json_encode($value), true);
//        $data = $resultArray;

        $data = array();

        foreach ($resultArray as $val){

            $templateDetails = array();

            $templateDetails ['Id'] = $val['Id'];
            $templateDetails ['Name'] = $val['Name'];
            $templateDetails ['Content'] = $val['Content'];
            $templateDetails ['HtmlContent'] = $val['HtmlContent'];
            $templateDetails ['IsActive'] = $val['IsActive'];
            $templateDetails ['ProposalType'] = array();
            $templateDetails ['ProposalType']['Id'] = $val['ProposalTypeId'];
            $templateDetails ['ProposalType']['Name'] = $val['ProposalTypeName'];
            $templateDetails ['ProposalType']['Code'] = $val['ProposalTypeCode'];


            array_push($data,$templateDetails);
        }



        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Templates fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Template not found'], 200);
        }
    }


    //template list count API

    function TemplateCount(Request $request)
    {

        error_log('in controller');

        $searchKeyword = $request->input('searchKeyword');
        $userId = $request->input('userId');

        //Fetching user if looged in user is belonging to admin
        $userData = UserModel::GetSingleUserViaId($userId);
        if (count($userData) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        } else {
            //Means user data fetched
            $val = TemplateModel::TemplateCountWithSearch("proposal_template", $searchKeyword);

            return response()->json(['data' => $val, 'message' => 'Templates count'], 200);

        }
    }

    //template list via pagination
    function TemplateListViaPagination(Request $request)
    {

        error_log('in controller');

        $pageNo = $request->input('pageNo');
        $limit = $request->input('limit');
        $searchKeyword = $request->input('searchKeyword');
        $userId = $request->input('userId');

        //Fetching user if looged in user is belonging to admin
        $userData = UserModel::GetSingleUserViaId($userId);
        if (count($userData) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        } else {
            //Means user data fetched

            $value = TemplateModel::FetchTemplateWithSearchAndPagination("proposal_template", $pageNo, $limit, $searchKeyword);

            $resultArray = json_decode(json_encode($value), true);
//            $data = $resultArray;

            $data = array();

            foreach ($resultArray as $val){

                $templateDetails = array();

                $templateDetails ['Id'] = $val['Id'];
                $templateDetails ['Name'] = $val['Name'];
                $templateDetails ['Content'] = $val['Content'];
                $templateDetails ['HtmlContent'] = $val['HtmlContent'];
                $templateDetails ['IsActive'] = $val['IsActive'];
                $templateDetails ['ProposalType'] = array();
                $templateDetails ['ProposalType']['Id'] = $val['ProposalTypeId'];
                $templateDetails ['ProposalType']['Name'] = $val['ProposalTypeName'];
                $templateDetails ['ProposalType']['Code'] = $val['ProposalTypeCode'];


                array_push($data,$templateDetails);
            }



            error_log(count($data));
            if (count($data) > 0) {
                return response()->json(['data' => $data, 'message' => 'Templates fetched successfully'], 200);
            } else {
                return response()->json(['data' => null, 'message' => 'Templates not found'], 200);
            }

        }
    }

    function GetSingleTemplateViaId(Request $request)
    {
        error_log('in controller');

        $id = $request->get('templateId');

        $val = TemplateModel::GetSingleTemplateViaId($id);

        error_log('val');
//        error_log($val);
        if ($val != null) {


            $templateDetails = array();

            $templateDetails ['Id'] = $val->Id;
            $templateDetails ['Name'] = $val->Name;
            $templateDetails ['Content'] = $val->Content;
            $templateDetails ['HtmlContent'] = $val->HtmlContent;
            $templateDetails ['IsActive'] = $val->IsActive;
            $templateDetails ['ProposalType'] = array();
            $templateDetails ['ProposalType']['Id'] = $val->ProposalTypeId;
            $templateDetails ['ProposalType']['Name'] = $val->ProposalTypeName;
            $templateDetails ['ProposalType']['Code'] = $val->ProposalTypeCode;

//            if ($templateDetails != null) {
            return response()->json(['data' => $templateDetails, 'message' => 'Template detail fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Template detail not found'], 200);
        }
    }

    function TemplateAdd(Request $request)
    {
        error_log('In controller');
        //Binding data to variable.
        $name = $request->get('Name');
        $proposalTypeId = $request->get('ProposalTypeId');
        $htmlContent = $request->get('HtmlContent');

        //First get and check if name exists or not
//        $checkEmail = UserModel::isDuplicateEmail($name);
//
//        error_log('Checking name exsist' . $checkEmail);
//
//        if (count($checkEmail) > 0) {
//            return response()->json(['data' => null, 'message' => 'Email already exists'], 400);
//        }


        $dataToInsert = array(
            "Name" => $name,
            "ProposalTypeId" => $proposalTypeId,
            "HtmlContent" => $htmlContent,
            "IsActive" => true
        );

//        error_log('$dataToInsert');
//        error_log($dataToInsert);
//        return response()->json(['data' => null, 'message' => 'Email already exists'], 400);

        DB::beginTransaction();

        $insertedRecord = GenericModel::insertGenericAndReturnID('proposal_template', $dataToInsert);
        error_log('Inserted record id ' . $insertedRecord);

        if ($insertedRecord == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Error in user registration'], 400);
        }

        DB::commit();
//        $emailMessage = "Welcome, You are successfully registered to CCM as ' .$roleName. ', use this password to login ' . $defaultPassword";
        //Now sending email
//      GenericModel::sendEmail($emailAddress, $emailMessage, null);

        return response()->json(['data' => $insertedRecord, 'message' => 'Template successfully added'], 200);

    }

    function TemplateUpdate(Request $request)
    {

        error_log("TemplateUpdate");

        $id = $request->get('Id');
        $name = $request->get('Name');
        $proposalTypeId = $request->get('ProposalTypeId');
        $htmlContent = $request->get('HtmlContent');


        //First get and check if record exists or not
//        $data = TemplateModel::GetSingleTemplate($id);
        $data = TemplateModel::GetSingleTemplateViaId($id);

//        error_log("ss", $data);

        if ($data == null) {
            return response()->json(['data' => null, 'message' => 'Template not found'], 400);
        }

        //We have get the data.
        //Now insert that data in log table to maitain old record of that user


        DB::beginTransaction();


        $dataToUpdate = array(
            "Name" => $name,
            "ProposalTypeId" => $proposalTypeId,
            "HtmlContent" => $htmlContent,
            "IsActive" => 1
        );

//        print_r($$dataToUpdate);
//        \Log::info('This is some useful information.');
//        error_log('template $dataToUpdate is : ' . $$dataToUpdate);

//        $emailMessage = "Dear User <br/>Update is made on your records";

        $update = GenericModel::updateGeneric('proposal_template', 'Id', $id, $dataToUpdate);

        error_log('template $update is : ' . $update);
        if ($update > 0) {
            DB::commit();
//            UserModel::sendEmail($data[0]->EmailAddress, $emailMessage, null);
            return response()->json(['data' => null, 'message' => 'template successfully updated'], 200);
        } else {
            DB::rollBack();
            return response()->json(['data' => null, 'message' => 'Error in updating template record'], 400);
        }
    }

    function TemplateDelete(Request $request)
    {
        error_log('in controller');
        $id = $request->get('id');

        //First get and check if record exists or not
        $getUser = UserModel::getUserViaId($id);

        if (count($getUser) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }
        //Binding data to variable.
        $dataToUpdate = array(
            "IsActive" => false
        );

        $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

        //now delete the account_invitation
        //of this email

        $updateAccountInvitation = GenericModel::updateGeneric('account_invitation', 'ToEmailAddress', $getUser[0]->EmailAddress, $dataToUpdate);

        if ($update == true) {
            return response()->json(['data' => $id, 'message' => 'Deleted successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Error in deleting'], 400);
        }
    }


}
