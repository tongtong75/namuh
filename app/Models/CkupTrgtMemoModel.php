<?php

namespace App\Models;

use CodeIgniter\Model;

class CkupTrgtMemoModel extends Model
{
    protected $table            = 'CKUP_TRGT_MEMO';
    protected $primaryKey       = 'CKUP_TRGT_MEMO_SN';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['CKUP_TRGT_SN', 'MEMO', /* 'REG_ID', 'MDFCN_ID' - 필요시 추가 */];

    // Dates (CKUP_TRGT_MEMO 테이블에 REG_YMD, MDFCN_YMD가 있다면)
    // protected $useTimestamps = true;
    // protected $createdField  = 'REG_YMD';
    // protected $updatedField  = 'MDFCN_YMD';

    // Callbacks (등록자/수정자 ID, 날짜 자동 기입용)
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = ['setMemoUserDetails'];
    // protected $beforeUpdate   = ['setMemoUserDetailsUpdate'];

    /*
    protected function setMemoUserDetails(array $data): array {
        $currentUserId = session()->get('user_id') ?? 'SYSTEM_USER';
        if (isset($data['data'])) {
            $data['data']['REG_ID'] = $currentUserId;
            $data['data']['MDFCN_ID'] = $currentUserId;
            // $data['data']['REG_YMD'] = date('Y-m-d H:i:s');
            // $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
    protected function setMemoUserDetailsUpdate(array $data): array {
        $currentUserId = session()->get('user_id') ?? 'SYSTEM_USER';
        if (isset($data['data'])) {
            $data['data']['MDFCN_ID'] = $currentUserId;
            // $data['data']['MDFCN_YMD'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
    */


    public function getMemoByTargetSn($targetSn)
    {
        return $this->where('CKUP_TRGT_SN', $targetSn)
                    ->orderBy('CKUP_TRGT_MEMO_SN', 'DESC') // 여러 개일 경우 최신 것 또는 첫 번째 것
                    ->first();
    }

    public function saveMemo(array $data)
    {
        // $data에는 CKUP_TRGT_SN, MEMO 포함
        $existingMemo = $this->where('CKUP_TRGT_SN', $data['CKUP_TRGT_SN'])->first();

        if ($existingMemo) { // 수정
            return $this->update($existingMemo['CKUP_TRGT_MEMO_SN'], ['MEMO' => $data['MEMO']]);
        } else { // 등록
            if (empty(trim($data['MEMO']))) { // 빈 메모는 저장하지 않음 (선택 사항)
                return true; // 또는 오류 반환
            }
            return $this->insert($data);
        }
    }

    public function deleteMemo($memoSn)
    {
        return $this->delete($memoSn);
    }

    public function deleteMemoByTargetSn($targetSn)
    {
        return $this->where('CKUP_TRGT_SN', $targetSn)->delete();
    }
}