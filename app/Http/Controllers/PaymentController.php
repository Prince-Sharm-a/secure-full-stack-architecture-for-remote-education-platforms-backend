<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    //
    public function createOrder(Request $request){
        try{
            $rules = [
                'course_id' => 'required'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();
            $course = Course::findOrFail($request->course_id);

            $enrollment = Enrollment::where('user_id','=',$user->id)->where('course_id','=',$request->course_id)->first();
            if($enrollment){
                return response()->json(['success'=>false,'message'=>'','data'=>[],'action'=>'enrolled']);
            }

            $data = Payment::create([
                'user_id'=>$user->id,
                'course_id'=>$course->id,
                'amount'=>$course->price
            ]);

            $api = new Api(env('ROZARPAY_KEY_ID'), env('ROZARPAY_SECRET'));

			$response = $api->order->create(array(
		                'receipt' => "BOOKING_".$data->id,
		                'amount' => $data->amount * 100, 
		                'currency' => 'INR', 
		                'notes'=> array(
		                    'booking_id' => $data->id,
		                    'course_id' => $data->course_id,
		                    'email' => $user->email,
                            'phone' => $user->phone,
                            'name' => $user->name,
		                )
		            ));

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $response->toArray()
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function verifyPayment(Request $request){
        try {
            $rules = [
                'booking_id' => 'required',
                'razorpay_order_id' => 'required',
                'razorpay_payment_id' => 'required',
                'razorpay_signature' => 'required',
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }

            $api = new Api(env('ROZARPAY_KEY_ID'), env('ROZARPAY_SECRET'));

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];
            $payment = Payment::findOrFail($request->booking_id);
            $payment->update(['status'=>'failed']);

            $api->utility->verifyPaymentSignature($attributes);

            // PAYMENT VERIFIED
            $payment->update(['status'=>'success']);

            // Save payment in DB here
            $data = Enrollment::create([
                'user_id'=>$payment->user_id,
                'course_id'=>$payment->course_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'enrollment_id' => $data->id
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ],500);

        }
    }
}
