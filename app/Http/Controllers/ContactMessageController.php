<?php

namespace App\Http\Controllers;

use App\Enums\ContactMessages\RequestStatus;
use App\Http\Requests\UpdateContactMessageRequestStatusRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function __construct(public ContactMessageService $contactMessageService) {}

    public function index(Request $request): Response
    {
        $search = (string) $request->input('search', '');
        $statusFilter = in_array($request->input('status'), ['all', 'read', 'unread']) ? $request->input('status') : 'all';
        $requestStatusFilter = in_array($request->input('request_status'), ['all', ...RequestStatus::values()], true)
            ? (string) $request->input('request_status')
            : 'all';

        $contactMessages = $this->contactMessageService->getPaginatedContactMessages(
            search: $search,
            statusFilter: $statusFilter,
            requestStatusFilter: $requestStatusFilter,
        );

        return Inertia::render('ContactMessages/ContactMessagesPage', [
            'contactMessages' => $contactMessages,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
                'request_status' => $requestStatusFilter,
            ],
            'requestStatuses' => RequestStatus::toArray(),
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $this->contactMessageService->markAsRead(contactMessage: $contactMessage);

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function markAsUnread(ContactMessage $contactMessage)
    {
        $this->contactMessageService->markAsUnread(contactMessage: $contactMessage);

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function updateRequestStatus(
        UpdateContactMessageRequestStatusRequest $request,
        ContactMessage $contactMessage
    ) {
        $this->contactMessageService->updateRequestStatus(
            contactMessage: $contactMessage,
            requestStatus: RequestStatus::from($request->validated('request_status')),
        );

        return redirect()->back()->with('success', 'تم التحديث بنجاح');
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $this->contactMessageService->delete(contactMessage: $contactMessage);

        return redirect()->back()->with('success', 'تم الحذف بنجاح');
    }
}
