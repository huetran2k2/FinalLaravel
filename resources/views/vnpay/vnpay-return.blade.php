@extends('master')
@section('content')
<div class="container">
 <div class="header clearfix">
 <h3 class="text-muted">Thông tin đơn hàng</h3>
 </div>
 <div class="table-responsive">
 <div class="form-group">
 <label >Mã đơn hàng:</label>
 
 <label>{{ $vnpay_Data['vnp_TxnRef'] }}</label>
 </div> 
 <div class="form-group">
 <label >Số tiền:</label>
 <label>{{ $vnpay_Data['vnp_Amount']/100 }} VNĐ</label>
 </div> 
 <div class="form-group">
 <label >Nội dung thanh toán:</label>
 <label>{{ $vnpay_Data['vnp_OrderInfo'] }}</label>
 </div> 
 <div class="form-group">
 <label >Mã phản hồi (vnp_ResponseCode):</label>
 <label>{{ $vnpay_Data['vnp_ResponseCode'] }}</label>
 </div> 
 <div class="form-group">
 <label >Mã GD Tại VNPAY:</label>
 <label>{{ $vnpay_Data['vnp_TransactionNo'] }}</label>
 </div> 
 <div class="form-group">
 <label >Mã Ngân hàng:</label>
 <label>{{ $vnpay_Data['vnp_BankCode'] }}</label>
 </div> 
 <div class="form-group">
 <label >Thời gian thanh toán:</label>
 <label>{{ $vnpay_Data['vnp_PayDate'] }}</label>
 </div> 
@endsection
