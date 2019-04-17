<?php

namespace App\Models;

use App\Models\GenericModel;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HelperModel;

use Mail;

class TemplateModel
{

    static public function FetchTemplateWithSearchAndPagination($tableName, $pageNo, $limit, $searchKeyword)
    {
        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword is NOT NULL');
            return DB::table($tableName)
//                ->select('*')
                ->select('proposal_template.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal_template.ProposalTypeId')
                ->where('proposal_template.IsActive', '=', true)
                ->where('proposal_template.Name', 'like', '%' . $searchKeyword . '%')
//                ->orWhere('LastName', 'like', '%' . $searchKeyword . '%')
//                    ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal_template.Id", 'DESC')
                ->get();

        } else {
            error_log('keyword is NULL');
            return DB::table($tableName)
//                ->select('*')
                ->select('proposal_template.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
                ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal_template.ProposalTypeId')
                ->where('proposal_template.IsActive', '=', true)
//                    ->offset($offset)->limit($limit)
                ->skip($pageNo * $limit)->take($limit)
                ->orderBy("proposal_template.Id", 'DESC')
                ->get();
        }
    }

    static public function TemplateCountWithSearch($tableName, $searchKeyword)
    {


        if ($searchKeyword != null && $searchKeyword != "null") {
            error_log('keyword not null');
            return DB::table($tableName)
                ->where("IsActive", "=", true)
                ->where('Name', 'like', '%' . $searchKeyword . '%')
//                ->orWhere('LastName', 'like', '%' . $keyword . '%')
                ->count();

        } else {
            error_log('keyword is null');
            return DB::table($tableName)
                ->where("IsActive", "=", 1)
                ->count();
        }
    }

    static public function GetSingleTemplateViaId($id)
    {
        error_log('## in model ##');
        error_log('## GetSingleTemplateViaId ##');
        error_log($id);
//        DB::enableQueryLog();

        $query = DB::table('proposal_template')
            ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal_template.ProposalTypeId')
//            ->select('proposal_template.*', 'proposal_type.Id as ProposalTypeId', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
            ->select('proposal_template.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
            ->where('proposal_template.Id', '=', $id)
            ->where('proposal_template.IsActive', '=', 1)
            ->first();


//        dd(DB::getQueryLog());

        return $query;
    }

    static public function GetSingleTemplate($id)
    {
        error_log('## in model ##');
        error_log('## GetSingleTemplate ##');
        error_log($id);

        $query = DB::table('proposal_template')
            ->select('*')
            ->where('Id', '=', $id)
            ->where('IsActive', '=', 1)
            ->first();

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


    static public function getTemplateList()
    {
        return DB::table('proposal_template')
//            ->select('*')
            ->select('proposal_template.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
            ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal_template.ProposalTypeId')
            ->where('proposal_template.IsActive', '=', 1)
            ->orderBy('proposal_template.Id', 'DESC')
            ->get();
    }

    static public function getTemplateListViaType($typeId)
    {
        return DB::table('proposal_template')
//            ->select('*')
            ->select('proposal_template.*', 'proposal_type.Name as ProposalTypeName', 'proposal_type.Code as ProposalTypeCode')
            ->leftJoin('proposal_type', 'proposal_type.Id', 'proposal_template.ProposalTypeId')
            ->where('proposal_template.IsActive', '=', 1)
            ->where('proposal_type.Id', '=', $typeId)
            ->orderBy('proposal_template.Id', 'DESC')
            ->get();
    }

}
