<?php

namespace App\Policies;

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ItemPolicy
{
    public function update(User $user, Item $item): Response
    {
        if ($user->id !== $item->user_id) {
            return Response::deny('이 상품을 수정할 수 없습니다.');
        }

        if ($item->status !== ItemStatus::OnSale) {
            return Response::deny('현재 상태에서는 수정할 수 없습니다.');
        }

        return Response::allow();
    }

    public function delete(User $user, Item $item): Response
    {
        if ($user->id !== $item->user_id) {
            return Response::deny('이 상품을 삭제할 수 없습니다.');
        }

        if ($item->status !== ItemStatus::OnSale) {
            return Response::deny('현재 상태에서는 삭제할 수 없습니다.');
        }

        return Response::allow();
    }

    public function changeStatus(User $user, Item $item): Response
    {
        if ($user->id !== $item->user_id) {
            return Response::deny('이 상품의 상태를 변경할 수 없습니다.');
        }

        return Response::allow();
    }
}
