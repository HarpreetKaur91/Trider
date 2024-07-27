<?php

namespace App\Http\Controllers\API\Booking;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingService;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
class BookingController extends Controller
{
    public function booking(Request $request){
        try{
            $user = User::whereHas('roles',function($q){ $q->where('role_name','user'); })->find($request->user()->id);
            if(!is_null($user))
            {
                $validator = Validator::make($request->all(), [
                    'services' => 'present|array',
                    'services.*.id' => 'required|integer|exists:services,id',
                    'services.*.qty' => 'required|numeric',
                    'services.*.hours' => 'required|integer',
                    'services.*.days' => 'required|integer',
                    'business_id' => 'required|integer|exists:users,id',
                    'booking_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                    'booking_time'=> 'required|date_format:h:i',
                    'payment_status' => 'required|in:online,cod',
                ]);
                if ($validator->fails())
                {
                    return response()->json(['success' => false,'message' => $validator->errors()->first()]);
                }
                else
                {
                    $business = User::whereHas('roles',function($q){ $q->whereIn('role_name',['company','freelancer']);})->find($request->business_id);
                    if(!is_null($business)):
                        $check_booking_date = Booking::where('business_id',$business->id)->where('booking_date',$request->booking_date)->where('booking_status','new')->get();
                        if(count($check_booking_date)>0):
                            return response()->json(['success' => false,'message' => 'Business has been already booked on this date']);
                        endif;

                        if(count($request->services)>0):
                            $data = [];
                            foreach($request->services as $key=>$value):
                                $service = Service::with('service_prices')->find($value['id']);
                                if($service){
                                    if($value['hours'] >= 1){
                                        foreach($service->service_prices as $service_price){
                                            if($service_price->shift == $value['hours']){
                                                $array=[];
                                                $total_amount = $value['qty']*$service_price->price;
                                                $array['service_id'] = $service->id;
                                                $array['qty'] = $value['qty'];
                                                $array['total_amount'] = $total_amount;
                                                $array['amount'] = $service_price->price;
                                                $array['hours'] = $value['hours'];
                                                $array['days'] = $value['days'];
                                                array_push($data,$array);
                                            }
                                        }
                                    }
                                    else{
                                        foreach($service->service_prices as $service_price){
                                            $hours = $value['days'] == 1 ? 1 : $value['days'] * 9;
                                            if($service_price->shift == 9){
                                                $array=[];
                                                $hours_amount = $hours * $service_price->price;
                                                $total_amount = $value['qty']*$hours_amount;
                                                $array['service_id'] = $service->id;
                                                $array['qty'] = $value['qty'];
                                                $array['total_amount'] = $total_amount;
                                                $array['amount'] = $service_price->price;
                                                $array['hours'] = $value['hours'];
                                                $array['days'] = $value['days'];
                                                array_push($data,$array);
                                            }
                                        }
                                    }
                                }
                            endforeach;
                            if(count($data)>0):
                                $get_total_amount =  array_sum(array_column($data,'total_amount'));
                                $booking = Booking::create([
                                    'user_id'=>$user->id,
                                    'business_id'=>$request->business_id,
                                    'booking_date'=>$request->booking_date,
                                    'booking_time'=>$request->booking_time,
                                    'payment_status'=>$request->payment_status,
                                    'order_no'=>'#'.mt_rand(100000,999999),
                                    'total_amount'=>$get_total_amount,
                                    'amount'=>$get_total_amount,
                                    'grand_amount'=>$get_total_amount,
                                    'tax'=>0,
                                ]);
                                foreach($data as $dt){
                                    BookingService::create([
                                        'user_id'=>$user->id,
                                        'service_id'=>$dt['service_id'],
                                        'booking_id'=>$booking->id,
                                        'qty'=>$dt['qty'],
                                        'hours'=>$dt['hours'],
                                        'days'=>$dt['days'],
                                        'total_amount'=>$dt['total_amount'],
                                        'amount'=>$dt['amount']
                                    ]);
                                }
                                return response()->json(['success'=>true,'message'=>'New Booking has been created successfully.']);
                            else:
                                return response()->json(['success'=>false,'message'=>'Something problem while booking.']);
                            endif;
                        endif;
                    else:
                        return response()->json(['success'=>false,'message'=>'Business not found']);
                    endif;
                }
            }
            else
            {
                return response()->json(['success'=>false,'message'=>'User not found']);
            }
        }
        catch(\Exception $e){
            $array = ['request'=>'add booking api','message'=>$e->getMessage()];
            \Log::info($array);
            return response()->json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
