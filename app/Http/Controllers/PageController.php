<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Facades\Date;
use App\Models\Slide;
use App\Models\Product;
use App\Models\Comment;
use App\Models\Customer;
use App\Models\bill;
use App\Models\BillDetail;
use App\Models\Wishlist;



use App\Models\ProductType;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class PageController extends Controller
{
    public function getIndex()
    {
        $slide = Slide::all();
        $new_product  = Product::where('new', 1)->paginate(4);
        $promotion_product = Product::where('promotion_price', '<>', 0)->paginate(8);
        $countnoibat_Pro = Product::where('new', 0)->count();
        $countNewPro = Product::where('new', 1)->count();
        return view('page.trangchu', compact('slide', 'new_product', 'countNewPro', 'countnoibat_Pro', 'promotion_product'));
    }
    public function getLoaiSp($type)
    {
        $type_product = ProductType::all();
        $sp_theoloai = Product::where('id_type', $type)->get();
        $sp_khac =  Product::where('id_type', '<>', $type)->paginate(3);
        return view('page.loai_sanpham', compact('sp_theoloai', 'type_product', 'sp_khac'));
    }
    // --------------------CONTACT AND ABOUT-------
    public function getContact()
    {
        return view('page.contact');
    }
    public function getAbout()
    {
        return view('page.about');
    }
    public function getAdminpage()
    {
        return view('pageAdmin.formAdd');
    }
    public function getIndexAdmin()
    {
        $products = product::all();
        return view('pageAdmin.admin', compact('products'));
    }
    public function postAdminAdd(Request $request)
    {
        $product = new Product();
        if ($request->hasFile('inputImage')) {
            $file = $request->file('inputImage');
            $fileName = $file->getClientOriginalName('inputImage');
            $file->move('source/image/product', $fileName);
        }
        $fileName = null;
        if ($request->file('inputImage') != null) {
            $file_name = $request->file('inputImage')->getClientOriginalName();
        }
        $product->name = $request->inputName;
        $product->image = $file_name;
        $product->description = $request->inputDescription;
        $product->unit_price = $request->inputPrice;
        $product->promotion_price = $request->inputPromotionPrice;
        $product->unit = $request->inputUnit;
        $product->new = $request->inputNew;
        $product->id_type = $request->inputType;
        $product->save();
        return redirect('/showadmin')->with('success', 'Đăng ký thành công');
    }
    public function formEdit()
    {
        return view('pageAdmin.formEdit');
    }

    public function getAdminEdit($id)
    {
        $product = product::find($id);
        return view('pageAdmin.formEdit')->with('product', $product);
    }

    public function postAdminEdit(Request $request)
    {
        $id = $request->editId;

        $product = product::find($id);
        if ($request->hasFile('editImage')) {
            $file = $request->file('editImage');
            $fileName = $file->getClientOriginalName('editImage');
            $file->move('source/image/product', $fileName);
        }
        if ($request->file('editImage') != null) {
            $product->image = $fileName;
        }
        $product->name = $request->editName;
        // $product->image=$file_name;
        $product->description = $request->editDescription;
        $product->unit_price = $request->editPrice;
        $product->promotion_price = $request->editPromotionPrice;
        $product->unit = $request->editUnit;
        $product->new = $request->editNew;
        $product->id_type = $request->editType;
        $product->save();
        return redirect('/showadmin');
    }
    public function postAdminDelete($id)
    {
        $product = product::find($id);
        $product->delete();
        return redirect('/showadmin');
    }
    public function getDetail(Request $request)
    {
        $sanpham = Product::where('id', $request->id)->first();
        $splienquan = Product::where('id', '<>', $sanpham->id, 'and', 'id_type', '=', $sanpham->id_type,)->paginate(3);
        $comments = Comment::where('id_product', $request->id)->get();
        return view('page.chitiet_sanpham', compact('sanpham', 'splienquan', 'comments'));
    }
    //------------------------- CART --------------------------
    public function getAddToCart(Request $req, $id)
    {
        if (Session::has('user')) {
            if (Product::find($id)) {
                $product = Product::find($id);
                $oldCart = Session('cart') ? Session::get('cart') : null;
                $cart = new Cart($oldCart);
                $cart->add($product, $id);
                $req->session()->put('cart', $cart);
                return redirect()->back();
            } else {
                return '<script>alert("Không tìm thấy sản phẩm này.");window.location.assign("/");</script>';
            }
        } else {
            return '<script>alert("Vui lòng đăng nhập để sử dụng chức năng này.");window.location.assign("/login");</script>';
        }
    }
    public function getDelItemCart($id)
    {
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->removeItem($id);
        if (count($cart->items) > 0 && Session::has('cart')) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        return redirect()->back();
    }
    // ------------------------ CHECKOUT -------------------																	
    public function getCheckout()
    {
        if (Session::has('cart')) {
            $oldCart = Session::get('cart');
            $cart = new Cart($oldCart);
            return view('page.checkout')->with([
                'cart' => Session::get('cart'),
                'product_cart' => $cart->items,
                'totalPrice' => $cart->totalPrice,
                'totalQty' => $cart->totalQty
            ]);;
        } else {
            return redirect('');
        }
        // Payment with vnpay
        if ($req->payment_method == "vnpay") {
            $cost_id = date_timestamp_get(date_create());                                    //Số hóa đơn					
            $vnp_TmnCode = "A0XRU83L"; //Mã website tại VNPAY														
            $vnp_HashSecret = "WKLIPDZGMITJLGFIESTBOBLOGMHCJWZN"; //Chuỗi bí mật														
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://localhost:8000/return-vnpay";
            $vnp_TxnRef = date("YmdHis"); //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY														
            $vnp_OrderInfo = "Thanh toán hóa đơn phí dich vụ";
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $bill->total * 100;
            $vnp_Locale = 'vn';
            $vnp_IpAddr = request()->ip();
            $vnp_BankCode = 'NCB';
            $inputData = array(
                "vnp_Version" => "2.0.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );
            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . $key . "=" . $value;
                } else {
                    $hashdata .= $key . "=" . $value;
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);														
                $vnpSecureHash = hash('sha256', $vnp_HashSecret . $hashdata);
                $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
            }
            echo '<script>location.assign("' . $vnp_Url . '");</script>';
            $this->apSer->thanhtoanonline($cost_id);
            return redirect('success')->with('data', $inputData);
        } else {
            echo "<script>alert('Đặt hàng thành công')</script>";
            return redirect('');
        }
    }
    public function postCheckout(Request $req)
    {
        if ($req->input('payment_method') != "VNPAY") {

            $cart = Session::get('cart');
            $customer = new Customer;
            $customer->name = $req->full_name;
            $customer->gender = $req->gender;
            $customer->email = $req->email;
            $customer->address = $req->address;
            $customer->phone_number = $req->phone;
            if (isset($req->notes)) {
                $customer->note = $req->notes;
            } else {
                $customer->note = "Không có ghi chú gì";
            }
            $customer->save();
            $bill = new bill;
            $bill->id_customer = $customer->id;
            $bill->date_order = date('Y-m-d');
            $bill->total = $cart->totalPrice;
            $bill->payment = $req->payment_method;
            if (isset($req->notes)) {
                $bill->note = $req->notes;
            } else {
                $bill->note = "Không có ghi chú gì";
            }
            $bill->save();
            foreach ($cart->items as $key => $value) {
                $bill_detail = new BillDetail;
                $bill_detail->id_bill = $bill->id;
                $bill_detail->id_product = $key; //$value['item']['id'];																	
                $bill_detail->quantity = $value['qty'];
                $bill_detail->unit_price = $value['price'] / $value['qty'];
                $bill_detail->save();
            }
            Session::forget('cart');
            $wishlists = Wishlist::where('id_user', Session::get('user')->id)->get();
            if (isset($wishlists)) {
                foreach ($wishlists as $element) {
                    $element->delete();
                }
            }
        } else { //nếu thanh toán là vnpay
            $cart = Session::get('cart');
            return view('/vnpay/vnpay-index', compact('cart'));
        }
    }
    // ----------- PAYMENT WITH VNPAY -----------
    //     public function PaymentWithVNPay(){

    // }
    //hàm xử lý nút Xác nhận thanh toán trên trang vnpay-index.blade.php, hàm này nhận request từ trang vnpay-index.blade.php
    public function createPayment(Request $request)
    {
        $cart = Session::get('cart');
        $vnp_TxnRef = $request->transaction_id; //Mã giao dịch. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = $request->order_desc;
        $vnp_Amount = str_replace(',', '', $cart->totalPrice * 100);
        $vnp_Locale = $request->language;
        $vnp_BankCode = $request->bank_code;
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];


        $vnpay_Data = array(
            "vnp_Version" => "2.0.0",
            "vnp_TmnCode" => env('VNP_TMNCODE'),
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => route('vnpayReturn'),
            "vnp_TxnRef" => $vnp_TxnRef,

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $vnpay_Data['vnp_BankCode'] = $vnp_BankCode;
        }
        ksort($vnpay_Data);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($vnpay_Data as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . $key . "=" . $value;
            } else {
                $hashdata .= $key . "=" . $value;
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = env('VNP_URL') . "?" . $query;
        if (env('VNP_HASHSECRECT')) {
            // $vnpSecureHash = md5($vnp_HashSecret . $hashdata);
            //$vnpSecureHash = hash('sha256', env('VNP_HASHSECRECT'). $hashdata);
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, env('VNP_HASHSECRECT')); //  
            // $vnp_Url .= 'vnp_SecureHashType=SHA256&vnp_SecureHash=' . $vnpSecureHash;
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        //dd($vnp_Url);
        return redirect($vnp_Url);
        die();
    }

    //ham nhan get request tra ve tu vnpay
    public function vnpayReturn(Request $request)
    {
        //dd($request->all());
        // if($request->vnp_ResponseCode=='00'){
        //     $secureHash = $request->query('vnp_SecureHash');
        //     if ($secureHash == env('VNP_HASHSECRECT')) {
        //      $cart=Session::get('cart');

        //      //lay du lieu vnpay tra ve
        //      $vnpay_Data=$request->all();

        //      //insert du lieu vao bang payments
        //      //.........

        //     //truyen vnpay_Data vao trang vnpay_return
        //     return view('vnpay_return',compact('vnpay_Data'));
        //     }
        // }
        //PHIEEN BAN 2022
        $vnp_SecureHash = $request->vnp_SecureHash;
        //echo $vnp_SecureHash;
        $vnpay_Data = array();
        foreach ($request->query() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $vnpay_Data[$key] = $value;
            }
        }

        unset($vnpay_Data['vnp_SecureHash']);
        ksort($vnpay_Data);
        $i = 0;
        $hashData = "";
        foreach ($vnpay_Data as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, env('VNP_HASHSECRECT'));
        // echo $secureHash;


        if ($secureHash == $vnp_SecureHash) {
            if ($request->query('vnp_ResponseCode') == '00') {
                $cart = Session::get('cart');
                //lay du lieu vnpay tra ve
                $vnpay_Data = $request->all();

                //insert du lieu vao bang payments
                //.........

                //truyen vnpay_Data vao trang vnpay_return
                return view('/vnpay/vnpay-return', compact('vnpay_Data'));
            }
        }
    }

    public function getInputEmail(Request $req)
    {
        return view("emails/input-email");
    } //hết getInputEmail

    public function postInputEmail(Request $req)
    {
        $email = $req->txtEmail;
        //validate

        // kiểm tra có user có email như vậy không
        $user = User::where('email', $email)->get();
        //dd($user);
        if ($user->count() != 0) {
            //gửi mật khẩu reset tới email
            $sentData = [
                'title' => 'Mật khẩu mới của bạn là:',
                'body' => '123456'
            ];
            \Mail::to($email)->send(new \App\Mail\SendMail($sentData));
            // Session::flash('message', 'Send email successfully!');
            echo '<script>alert("Send email successfully!");window.location.assign("/");</script>';
        } else {
            // return redirect()->route('getInputEmail')->with('message', 'Your email is not right');
            echo '<script>alert("Your email is not right!");window.location.assign("/input-email");</script>';
        }
    }
}
