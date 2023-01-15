<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReportController
{

    public function secondReport(Request $request)
    {
        $request->validate([
            'date' => 'required',
        ]);
        $result = [];


        $invoice_id_ref =   DB::table('orders')->select(DB::raw('CONCAT(orders.date,orders.user_id) AS invoice_qty'))
            ->where('date', '=', $request->date)
            ->where('status', 3)
            ->get()->pluck('invoice_qty')->toArray();
        $invoice_id_ref = array_values(array_unique($invoice_id_ref));

        for ($i = 0; $i < count($invoice_id_ref); $i++) {
            $result[$i]['date'] = $request->date;
            $result[$i]['invoice_id_ref'] = $invoice_id_ref[$i];

            $orders = DB::table('orders')->select('*')->whereRaw('CONCAT(`date`,`user_id`) = ?', [$invoice_id_ref[$i]])->get();
            $total = 0;
            foreach ($orders as $order) {
                $total =  (DB::table('product')->select('price_after_discount')->where('id', $order->product_id)->first()->price_after_discount * $order->qty) + $total;
            }
            $result[$i]['total'] = $total;
        }

        return $result;
    }

    public function firstReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'to_date' => 'required',
        ]);
        $startDate = $request->start_date;
        $toDate = $request->to_date;

        $orders =    DB::table('orders')->select('*')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $toDate)
            ->where('status', 3)
            ->get();
        //    

        $dates = [];
        $result = [];
        $invoice_qty = [];
        // get date
        foreach ($orders as $order) {
            array_push($dates, $order->date);
        }
        $dates = array_values(array_unique($dates));
        //
        foreach ($dates as $date) {
            $result[count($result)]['date'] = $date;
        }

        for ($i = 0; $i < count($result); $i++) {
            $invoice_qty =   DB::table('orders')->select(DB::raw('CONCAT(orders.date,orders.user_id) AS invoice_qty'))
                ->where('date', '=', $result[$i]['date'])
                ->where('status', 3)
                ->get()->pluck('invoice_qty')->toArray();
            $invoice_qty = array_values(array_unique($invoice_qty));

            $result[$i]['invoice_qty'] = count($invoice_qty);

            $result[$i]['invoice_id_ref_list'] = $invoice_qty;


            //total

            $temp_order = DB::table('orders')->select('*')->where('date', $result[$i]['date'])->get();
            $total = 0;
            foreach ($temp_order as $order) {
                $total =  (DB::table('product')->select('price_after_discount')->where('id', $order->product_id)->first()->price_after_discount * $order->qty) + $total;
            }
            $result[$i]['total'] = $total;
        }
        return $result;
    }


    public function thirdReport(Request $request)
    {
        $request->validate([
            'invoice_id_ref' => 'required',
        ]);

        $orders = DB::table('orders')->select('*')->whereRaw('CONCAT(`date`,`user_id`) = ?', [$request->invoice_id_ref])->where('status', '3')->get();

        // return $orders;

       

        for ($i = 0; $i < count($orders); $i++) {

            $orders[$i]->user_name =  DB::table('user')->select('name')->where('id',$orders[$i]->user_id)->first()->name;
            $orders[$i]->phone =   DB::table('user')->select('phone')->where('id',$orders[$i]->user_id)->first()->phone;
            $orders[$i]->product_name =  DB::table('product')->select('name')->where('id',$orders[$i]->product_id)->first()->name;
            $orders[$i]->final_price =  DB::table('product')->select('price_after_discount')->where('id',$orders[$i]->product_id)->first()->price_after_discount;
            $orders[$i]->discount =  DB::table('product')->select('discount')->where('id',$orders[$i]->product_id)->first()->discount;
            unset($orders[$i]->user_id);
            unset($orders[$i]->id);
            unset($orders[$i]->product_id);
            unset($orders[$i]->color_id);
            unset($orders[$i]->delivery_type);
            unset($orders[$i]->status);
            unset($orders[$i]->address_id_ref);
        }

        return  $orders;
    }
}
