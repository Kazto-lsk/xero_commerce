<div class="title">{{ $title }}</div>
<div class="xe-well">
    <h2>주문실패</h2>
    <p>결제 단계에서 주문에 실패하였습니다. <a href="{{instance_route('xero_commerce::order.register.again', ['order' => $order->id])}}">다시시도</a> 또는
        <a href="{{instance_route('xero_commerce::cart.index')}}">장바구니</a>로 이동합니다.</p>
</div>
