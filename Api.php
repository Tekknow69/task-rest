<?php
class Api {

    private $url_order = "https://m1ylx2frpxud.c01-14.plentymarkets.com/rest/orders";
    //private $url_order2 = "https://m1ylx2frpxud.c01-14.plentymarkets.com/rest/orders?itemsPerPage=250&paidAtFrom=2021-08-01T00%3A00%3A00%2B02%3A00&paidAtTo=2021-09-01T00%3A00%3A00%2B02%3A00&addOrderItems=false&paymentStatus=fullyPaid";
    private $url_login = "https://m1ylx2frpxud.c01-14.plentymarkets.com/rest/login";

    // checking if user has accessToken if not then it is not logged in
    public function isLoggedIn() {
        if (isset($_SESSION["accessToken"]) && isset($_SESSION["tokenType"])) {
            return true;
        }
        return false;
    }

    // removing accessToken to log out
    public function logout() {
        unset($_SESSION["accessToken"]);
        unset($_SESSION["tokenType"]);
    }

    // calling login rest api for accesToken
    public function login($username, $password) {
        $data_array = array(
            'username' => $username,
            'password' => $password,
        );
        $data = http_build_query($data_array);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url_login);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($curl);

        if($e = curl_error($curl)) {
            echo $e;
        } else {

            $decoded = json_decode($res);

            if(!isset($decoded->accessToken)) {
                return false;
            }
            $_SESSION["accessToken"] = $decoded->accessToken;
            $_SESSION["tokenType"] = $decoded->tokenType;
        }
        curl_close($curl);
        return true;
    }

    // calling order rest api
    // this function calling itself if itemPerPage is less then total items 
    public function getOrderData($page, $itemsPerPage, $paidAtFrom, $paidAtTo, $paymentStatus, $data) {
        $data1 = $data;

        $url = $this->url_order;

        // url builder 
        $url .= !($page == '') ?  '?page='.urlencode($page) : '';
        $url .= !($itemsPerPage == '') ?  '?itemsPerPage='.urlencode($itemsPerPage) : '';

        $paidAtFrom1 = !($paidAtFrom == '') ? '&paidAtFrom='. urlencode($paidAtFrom.':00+02:00') : '';
        $url .= $paidAtFrom1;

        $paidAtTo1 = !($paidAtTo == '') ? '&paidAtTo='.urlencode($paidAtTo.':00+02:00') : '';
        $url .= $paidAtTo1;

        $url .= !($paymentStatus== '') ? '&paymentStatus='.urlencode($paymentStatus) : '';
        $url .= '&addOrderItems=false';
    
        $order_data;
        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Bearer '.$_SESSION["accessToken"];


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);

        if($e = curl_error($curl)) {
            echo $e;
        } else {
            $decoded = json_decode($res);
            $order_data = $decoded;
        }
        curl_close($curl);
        

        $order_count = count($order_data->entries);
        $sumNet = 0;
        $sumBruto = 0;

        // counting order items pricess
        for($i = 0; $i < $order_count; $i++) {

            $sumNet += $order_data->entries[$i]->amounts[0]->netTotal;
            $sumBruto += $order_data->entries[$i]->amounts[0]->grossTotal;
        }
        
        // forming output
        $outpu = [
            'totalsCount' => $order_count,
            'sumNet' => $sumNet,
            'sumBruto' => $sumBruto,
        ];

        // mergin data if it has more items then picked per page
        if(isset($data1)) {
            $outpu = [
                'totalsCount' => $order_count + $data['totalsCount'],
                'sumNet' => $sumNet + $data['sumNet'],
                'sumBruto' => $sumBruto + $data['sumBruto'],
            ];
        }        

        // checking if last page
        if(!($order_data->isLastPage) && $page <= $order_data->lastOnPage) {
            $page += 1;
            // calling itself to bring more order items
            return $this->getOrderData($page, $itemsPerPage, $paidAtFrom, $paidAtTo, $paymentStatus, $outpu);
        } else {    
            return $outpu;
        }

    }
}
?>