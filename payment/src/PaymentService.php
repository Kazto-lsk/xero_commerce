<?php


namespace Xpressengine\XePlugin\XeroPay;


use App\Facades\XeConfig;
use Illuminate\Support\Facades\Auth;
use Xpressengine\Http\Request;
use Xpressengine\XePlugin\XeroPay\Models\PayLog;
use Xpressengine\XePlugin\XeroPay\Models\Payment;

class PaymentService
{

    /**
     * @var PaymentHandler
     */
    protected $handler;

    public function __construct()
    {
        $this->handler = app('xero_pay::paymentHandler');
    }

    public function getPg()
    {
        return app('xe.pluginRegister')->get('xero_pay');
    }

    public function loadScript()
    {
        $this->handler->prepare();
    }

    /**
     * @param Request $request
     * @return PaymentRequest
     */
    public function formatRequest(Request $request)
    {
        $pay = $this->makePayment($request);
        $form = $this->handler->makePaymentRequest($request, $pay);
        $this->logPayment($pay, 'test', $request->all(), $form);
        return $form;
    }

    /**
     * @param Request $request
     * @return PaymentResponse
     */
    public function execute(Request $request)
    {
        //거래요청(고객->pg)
        $response = $this->handler->getResponse($request);
        if($response->fail()) return view('xero_commerce::payment.views.fail',['msg'=>$response->msg()]);


        //거래요청 후 승인 요청(상점->pg)
        $result = $this->handler->getResult($request);

        //승인 시
        if ($result->success()){
            return view('xero_commerce::payment.views.callback');
        }else {
            return view('xero_commerce::payment.views.fail',['msg'=>$result->msg()]);
//            return '<script>alert("' . $result->msg() . '"); parent.closeIframe();</script>';
        }
    }

    public function callback(Request $request)
    {
        return $this->handler->callback($request);
    }

    public function methodList()
    {
        return $this->handler->getMethodList();
    }

    private function makePayment(Request $request)
    {
        $pay = new Payment();
        $pay->user_id = 'test';
        $pay->ip = $request->ip();
        $pay->payment_type = XeConfig::getOrNew('xero_pay')->get('uses');
        $pay->payable_id = $request->get('target_id');
        $pay->price = $request->get('price');
        $pay->status = '';
        $pay->save();
        return $pay;
    }

    private function logPayment(Payment $payment, $status, $req =[], $res = [])
    {
        $log = new PayLog();
        $log->req = json_encode($req);
        $log->res = json_encode($res);
        $log->action = $status;
        $payment->log()->save($log);
        return $log;
    }
}
