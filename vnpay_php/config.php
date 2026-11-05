<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
  
$vnp_TmnCode = "VFTFARM1"; //Website ID in VNPAY System
$vnp_HashSecret = "TEGRSBSUETHJAEIIMHMSKPCUZRPBINVV"; //Secret key
$vnp_Url = "https://pay.vnpay.vn/vpcpay.html";
$vnp_Returnurl = "https://vftfarms.com/vnpay_php/vnpay_return/";
//$vnp_Returnurl = "http://localhost/fosacha/vnpay_php/vnpay_return/";
$vnp_apiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
//Config input format
//Expire
$startTime = date("YmdHis");
$expire = date('YmdHis',strtotime('+15 minutes',strtotime($startTime)));
