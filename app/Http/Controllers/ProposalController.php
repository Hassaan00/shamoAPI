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
use App\Models\ProposalModel;
use App\Models\GenericModel;
use App\Models\HelperModel;
use App\Models\ServicesModel;
use Config;
use Carbon\Carbon;

class ProposalController extends Controller
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


    //proposal list for combo box
    function ProposalList()
    {
        $value = ProposalModel::getProposalList();

        $resultArray = json_decode(json_encode($value), true);
//        $data = $resultArray;

        $data = array();

        foreach ($resultArray as $val) {

            $proposalDetails = array();

            $proposalDetails ['Id'] = $val['Id'];
            $proposalDetails ['Name'] = $val['Name'];
            $proposalDetails ['Date'] = $val['Date'];
            $proposalDetails ['ExpiryDate'] = $val['ExpiryDate'];
            $proposalDetails ['Offer'] = $val['Offer'];
            $proposalDetails ['IsActive'] = $val['IsActive'];
            $proposalDetails ['TemplateId'] = $val['ProposalTemplateId'];
            $proposalDetails ['TemplateContent'] = $val['TemplateContent'];

            $proposalDetails ['ProposalType'] = array();
            $proposalDetails ['ProposalType']['Id'] = $val['ProposalTypeId'];
            $proposalDetails ['ProposalType']['Name'] = $val['ProposalTypeName'];
            $proposalDetails ['ProposalType']['Code'] = $val['ProposalTypeCode'];

            array_push($data, $proposalDetails);
        }

        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Users fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Users not found'], 200);
        }
    }


    //proposal list count API

    function ProposalCount(Request $request)
    {

        error_log('in controller');

        $searchKeyword = $request->input('searchKeyword');
        $userId = $request->input('userId');

        //Fetching user if looged in user is belonging to admin
        $userData = UserModel::GetSingleUserViaId($userId);
        if (count($userData) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        } else {
            error_log('user data fetched');
            error_log($userData);
            //Means user data fetched
            if($userData[0]->RoleCodeName == "admin"){
                error_log('admin');

                $val = ProposalModel::ProposalCountWithSearch("proposal", $searchKeyword);

                return response()->json(['data' => $val, 'message' => 'Proposal count'], 200);
            }
            else{
                error_log('other');

                $val = ProposalModel::ProposalCountWithSearchViaUser("proposal", $searchKeyword, $userId);
                
                return response()->json(['data' => $val, 'message' => 'Proposal count'], 200);
            }

            

        }
    }

    //Proposal list via pagination
    function ProposalListViaPagination(Request $request)
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

            if($userData[0]->RoleCodeName == "admin"){
                $value = ProposalModel::FetchProposalWithSearchAndPagination("proposal", $pageNo, $limit, $searchKeyword);
            }
            else{
                $value = ProposalModel::FetchProposalWithSearchAndPaginationViaUser("proposal", $pageNo, $limit, $searchKeyword, $userId);
            }

            $resultArray = json_decode(json_encode($value), true);
            // $data = $resultArray;

            $data = array();

            foreach ($resultArray as $val) {

                $proposalDetails = array();

                $proposalDetails ['Id'] = $val['Id'];
                $proposalDetails ['Name'] = $val['Name'];
                $proposalDetails ['Date'] = $val['Date'];
                $proposalDetails ['ExpiryDate'] = $val['ExpiryDate'];
                $proposalDetails ['Offer'] = $val['Offer'];
                $proposalDetails ['IsActive'] = $val['IsActive'];
                $proposalDetails ['TemplateId'] = $val['ProposalTemplateId'];
                $proposalDetails ['TemplateContent'] = $val['TemplateContent'];

                $proposalDetails ['ProposalType'] = array();
                $proposalDetails ['ProposalType']['Id'] = $val['ProposalTypeId'];
                $proposalDetails ['ProposalType']['Name'] = $val['ProposalTypeName'];
                $proposalDetails ['ProposalType']['Code'] = $val['ProposalTypeCode'];

                array_push($data, $proposalDetails);
            }

            error_log(count($data));

            if (count($data) > 0) {
                return response()->json(['data' => $data, 'message' => 'Proposals fetched successfully'], 200);
            } else {
                return response()->json(['data' => null, 'message' => 'Proposals not found'], 200);
            }

        }
    }

    function GetSingleProposalViaId(Request $request)
    {
        error_log('in controller');

        $id = $request->get('proposalId');

        $val = ProposalModel::GetSingleProposalViaId($id);

        error_log('val');
//        error_log($val);
        if ($val != null) {
            $proposalDetails = array();

            $proposalDetails ['Id'] = $val->Id;
            $proposalDetails ['Name'] = $val->Name;
            $proposalDetails ['Date'] = $val->Date;
            $proposalDetails ['ExpiryDate'] = $val->ExpiryDate;
            $proposalDetails ['Offer'] = $val->Offer;
            $proposalDetails ['IsActive'] = $val->IsActive;
            $proposalDetails ['TemplateId'] = $val->ProposalTemplateId;
            $proposalDetails ['TemplateContent'] = $val->TemplateContent;

            $proposalDetails ['ProposalType'] = array();
            $proposalDetails ['ProposalType']['Id'] = $val->ProposalTypeId;
            $proposalDetails ['ProposalType']['Name'] = $val->ProposalTypeName;
            $proposalDetails ['ProposalType']['Code'] = $val->ProposalTypeCode;


            $proposalDetails ['ProposalTemplate']['Id'] = $val->ProposalTemplateId;
            $proposalDetails ['ProposalTemplate']['Name'] = $val->ProposalTemplateName;
            $proposalDetails ['ProposalTemplate']['Content'] = $val->ProposalTemplateContent;
            $proposalDetails ['ProposalTemplate']['HtmlContent'] = $val->ProposalTemplateHtmlContent;
            $proposalDetails ['ProposalTemplate']['IsActive'] = $val->ProposalTemplateIsActive;
            $proposalDetails ['ProposalTemplate']['ProposalType'] = array();
            $proposalDetails ['ProposalTemplate']['ProposalType']['Id'] = $val->TemplateProposalTypeId;
            $proposalDetails ['ProposalTemplate']['ProposalType']['Name'] = $val->TemplateProposalTypeName;
            $proposalDetails ['ProposalTemplate']['ProposalType']['Code'] = $val->TemplateProposalTypeCode;

            $proposalDetails ['ProposalSentTo'] = array();


            $val1 = ProposalModel::GetSingleProposalUser($id);


            if ($val1 != null) {

                $proposalDetails ['ProposalSentTo'] = $val1 ;

            }


//            if ($proposalDetails != null) {
            return response()->json(['data' => $proposalDetails, 'message' => 'Proposal detail fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Proposal detail not found'], 200);
        }
    }

    function ProposalAdd(Request $request)
    {
        error_log('In controller');
        DB::beginTransaction();

        try {

            

            //Binding data to variable.
            $userId = $request->get('userId');
            $name = $request->get('Name');
            $date = $request->get('Date');
            $expiryDate = $request->get('ExpiryDate');
            $offer = $request->get('Offer');
            $proposalTemplateId = $request->get('TemplateId');
            $templateContent = $request->get('TemplateContent');
            $proposalTypeId = $request->get('ProposalTypeId');

            $subCheck= ServicesModel::checkSubscription($userId, $proposalTypeId);

            if($subCheck == false){
                return response()->json(['data' => null, 'message' => 'You Dont Have Subscription'], 400);
            }


            $dataToInsert = array(
                "ProposalTemplateId" => $proposalTemplateId,
                "ProposalTypeId" => $proposalTypeId,
                "Name" => $name,
                "Date" => $date,
                "ExpiryDate" => $expiryDate,
                "Offer" => $offer,
                "TemplateContent" => $templateContent,
                "CreatedBy" => $userId,
                "IsActive" => true
            );

            error_log('$dataToInsert');
//            error_log($dataToInsert);
//            return response()->json(['data' => null, 'message' => 'Email already exists'], 400);


            $insertedRecord = GenericModel::insertGenericAndReturnID('proposal', $dataToInsert);
            error_log('Inserted record id ' . $insertedRecord);

            if ($insertedRecord == 0) {
                DB::rollback();
                return response()->json(['data' => null, 'message' => 'Error in saving data'], 400);
            } else {

                error_log('proposal data inserted');

                $sentToData = array();


                foreach ($request->input('SentTo') as $item) {
                    // if ($item['Id'] == null || $item['Id'] == 0) {
                    $data = array(
                        'ProposalId' => $insertedRecord,
                        'Name' => $item['Name'],
                        'Address' => $item['Address'],
                        'Country' => $item['Country'],
                        'State' => $item['State'],
                        'City' => $item['City'],
                        'OwnerType' => $item['OwnerType'],
                        'Email' => $item['Email'],
                        'Phone' => $item['Phone'],
                        'ZipCode' => $item['ZipCode'],
                        'LocalZoneCode' => $item['LocalZoneCode'],
                        'Date' => $item['Date'],
                        'ExpiryDate' => $item['ExpiryDate'],
                        'Offer' => $item['Offer']
                    );

                    array_push($sentToData, $data);
                    // }
                }

                error_log('$sentToData');

                $insertedData = GenericModel::insertGeneric('proposal_sent', $sentToData);
                if ($insertedData == false) {
                    error_log('data not inserted');
                    DB::rollback();
                    return response()->json(['data' => null, 'message' => 'Error in inserting immunization vaccine'], 400);
                } else {
                    error_log('data inserted');

                    DB::commit();
                    // $emailMessage = "Welcome, You are successfully registered to CCM as ' .$roleName. ', use this password to login ' . $defaultPassword";
                    //Now sending email
                    // GenericModel::sendEmail($emailAddress, $emailMessage, null);

                    return response()->json(['data' => $insertedRecord, 'message' => 'Proposal successfully added'], 200);
                }


            }

        } catch (Exception $e) {
            DB::rollback();
            error_log('error ' . $e);
            return response()->json(['data' => null, 'message' => 'Something went wrong'], 500);
        }

    }

    function ProposalUpdate(Request $request)
    {

        error_log("ProposalUpdate");

        $id = $request->get('Id');
        $name = $request->get('Name');
        $proposalTypeId = $request->get('ProposalTypeId');
        $htmlContent = $request->get('HtmlContent');


        //First get and check if record exists or not
//        $data = ProposalModel::GetSingleProposal($id);
        $data = ProposalModel::GetSingleProposalViaId($id);

//        error_log("ss", $data);

        if ($data == null) {
            return response()->json(['data' => null, 'message' => 'Proposal not found'], 400);
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
//        error_log('Proposal $dataToUpdate is : ' . $$dataToUpdate);

//        $emailMessage = "Dear User <br/>Update is made on your records";

        $update = GenericModel::updateGeneric('proposal', 'Id', $id, $dataToUpdate);

        error_log('Proposal $update is : ' . $update);
        if ($update > 0) {
            DB::commit();
//            UserModel::sendEmail($data[0]->EmailAddress, $emailMessage, null);
            return response()->json(['data' => null, 'message' => 'Proposal successfully updated'], 200);
        } else {
            DB::rollBack();
            return response()->json(['data' => null, 'message' => 'Error in updating Proposal record'], 400);
        }
    }

    function ProposalDelete(Request $request)
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
