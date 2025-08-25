<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Payment;
use App\Http\Requests\FormsRequest;
use App\Http\Requests\PaymentRequest;

class PaymentsController extends Controller
{
    public function addBusinessPermit(array $request) {
        return Payment::create($request);
    }

    public function fieldValidator(PaymentRequest $request): Response
    {
        return $request->validated();
    }
}
