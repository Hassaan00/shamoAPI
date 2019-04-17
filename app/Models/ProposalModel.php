<?php

namespace App\Models;

use App\Models\GenericModel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HelperModel;

use Mail;

class ProposalModel
{

    static public function FetchProposalWithSearchAndPagination($tableName, $pageNo, $limit, $searchKeyword)
    {
        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword is NOT NULL');
            return DB::table($tableName)
                // ->select('*')
                ->select('proposal.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal.ProposalTypeId')
                ->where('proposal.IsActive', '=', true)
                ->where('proposal.Name', 'like', '%' . $searchKeyword . '%')
                // ->orWhere('LastName', 'like', '%' . $searchKeyword . '%')
                // ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal.Id", 'DESC')
                ->get();

        } else {
            error_log('keyword is NULL');
            return DB::table($tableName)
                // ->select('*')
                ->select('proposal.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal.ProposalTypeId')
                ->where('proposal.IsActive', '=', true)
                // ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal.Id", 'DESC')
                ->get();
        }
    }

    static public function FetchProposalWithSearchAndPaginationViaUser($tableName, $pageNo, $limit, $searchKeyword, $userId)
    {
        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword is NOT NULL');
            return DB::table($tableName)
                // ->select('*')
                ->select('proposal.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal.ProposalTypeId')
                ->where('proposal.IsActive', '=', true)
                ->where('proposal.CreatedBy', '=', $userId)
                ->where('proposal.Name', 'like', '%' . $searchKeyword . '%')
                // ->orWhere('LastName', 'like', '%' . $searchKeyword . '%')
                // ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal.Id", 'DESC')
                ->get();

        } else {
            error_log('keyword is NULL');
            return DB::table($tableName)
                // ->select('*')
                ->select('proposal.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal.ProposalTypeId')
                ->where('proposal.IsActive', '=', true)
                ->where('proposal.CreatedBy', '=', $userId)
                // ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal.Id", 'DESC')
                ->get();
        }
    }

    static public function ProposalCountWithSearch($tableName, $searchKeyword)
    {


        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword not null');
            return DB::table($tableName)
                ->where("IsActive", "=", true)
                ->where('Name', 'like', '%' . $searchKeyword . '%')
                // ->orWhere('LastName', 'like', '%' . $keyword . '%')
                ->count();

        } else {
            error_log('keyword is null');
            return DB::table($tableName)
                ->where("IsActive", "=", 1)
                ->count();
        }
    }

    static public function ProposalCountWithSearchViaUser($tableName, $searchKeyword, $userId)
    {


        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword not null');
            return DB::table($tableName)
                ->where("IsActive", "=", true)
                ->where("CreatedBy", "=", $userId)
                ->where('Name', 'like', '%' . $searchKeyword . '%')
                // ->orWhere('LastName', 'like', '%' . $keyword . '%')
                ->count();

        } else {
            error_log('keyword is null');
            return DB::table($tableName)
                ->where("IsActive", "=", 1)
                ->where("CreatedBy", "=", $userId)
                ->count();
        }
    }

    static public function GetSingleProposalViaId($id)
    {
        error_log('## in model ##');
        error_log('## GetSingleProposalViaId ##');
        error_log($id);
//        DB::enableQueryLog();

        $query = DB::table('proposal')
            ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal.ProposalTypeId')
            ->leftJoin('proposal_template', 'proposal_template.Id', 'proposal.ProposalTemplateId')
            ->leftJoin('proposal_type as tpt', 'tpt.Id', 'proposal_template.Id')
//            ->select('proposal.*', 'proposal_type.Id as ProposalTypeId', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
            ->select(
                'proposal.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode',
                'proposal_template.Name as ProposalTemplateName', 'proposal_template.Content as ProposalTemplateContent',
                'proposal_template.HtmlContent as ProposalTemplateHtmlContent', 'proposal_template.IsActive as ProposalTemplateIsActive',
                'tpt.Id as TemplateProposalTypeId', 'tpt.Name as TemplateProposalTypeName',
                'tpt.Code as TemplateProposalTypeCode'
            )
            ->where('proposal.Id', '=', $id)
            ->where('proposal.IsActive', '=', 1)
            ->first();


//        dd(DB::getQueryLog());

        return $query;
    }

    static public function GetSingleProposal($id)
    {
        error_log('## in model ##');
        error_log('## GetSingleProposal ##');
        error_log($id);

        $query = DB::table('proposal')
            ->select('*')
            ->where('Id', '=', $id)
            ->where('IsActive', '=', 1)
            ->first();

        return $query;
    }

    static public function GetSingleProposalUser($id)
    {
        error_log('## in model ##');
        error_log('## GetSingleProposal ##');
        error_log($id);

        $query = DB::table('proposal_sent')
            ->select('*')
            ->where('ProposalId', '=', $id)
            ->where('IsActive', '=', 1)
            ->get();

        return $query;
    }

    public static function sendEmail($email, $emailMessage, $url)
    {
        $urlForEmail = url($url);

        Mail::raw($emailMessage, function ($message) use ($email) {
            $message->to($email)->subject("Invitation");
        });

        return true;
    }


    static public function getProposalList()
    {
        return DB::table('proposal')
            ->select('*')
            ->where('IsActive', '=', 1)
            ->orderBy('Id', 'DESC')
            ->get();
    }

}
