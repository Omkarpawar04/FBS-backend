<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquiryRequest;
use App\Repositories\Interfaces\EnquiryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Mail\EnquiryMail;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    protected $enquiryRepository;

    public function __construct(EnquiryRepositoryInterface $enquiryRepository)
    {
        $this->enquiryRepository = $enquiryRepository;
    }

    public function createEnquiry(EnquiryRequest $request): JsonResponse
    {
        try {
            $enquiry = $this->enquiryRepository->create($request->validated());
            Mail::to($enquiry->email_address)->send(new EnquiryMail());
            
            return response()->json([
                'message' => 'Enquiry created successfully.',
                'data' => $enquiry,
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Enquiry creation failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Request accepted successfully',
                'info' => 'We received your enquiry but are experiencing technical difficulties. We will process it soon.',
                'data' => $request->validated()
            ], 200);
        }
    }
    public function getAllEnquiries(): JsonResponse
    {
        $data = $this->enquiryRepository->getAllWithCount();

        return response()->json([
            'total_count' => $data['total_count'],
            'enquiries' => $data['enquiries'],
        ], 200);
    }

    public function addRemark(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'remark' => 'required|string',
        ]);

        $enquiry = $this->enquiryRepository->addRemark($id, $request->input('remark'));

        return response()->json([
            'message' => 'Remark added successfully.',
            'data' => $enquiry,
        ], 200);
    }

}
