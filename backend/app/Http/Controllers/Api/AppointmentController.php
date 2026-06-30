<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointmentService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Appointment::query()
            ->with([
                'property',
                'client',
                'owner',
                'provider',
                'contract',
                'mandate',
                'complaint',
                'collaboration',
                'creator',
            ])
            ->latest('start_at');

        foreach ([
            'status',
            'appointment_type',
            'property_id',
            'client_id',
            'owner_id',
            'provider_id',
            'contract_id',
            'mandate_id',
            'complaint_id',
            'collaboration_id',
        ] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_at', '>=', $request->date('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_at', '<=', $request->date('end_date'));
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . $request->string('keyword')->toString() . '%';

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('appointment_number', 'like', $keyword)
                    ->orWhere('appointment_type', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('description', 'like', $keyword)
                    ->orWhere('location', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('priority', 'like', $keyword);
            });
        }

        return AppointmentResource::collection(
            $query->paginate((int) $request->integer('per_page', 15))
        );
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $user = $request->user();
        $appointment = $this->appointmentService->createAppointment(
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return (new AppointmentResource($this->freshAppointment($appointment)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        return new AppointmentResource($this->freshAppointment($appointment));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): AppointmentResource
    {
        $user = $request->user();
        $appointment = $this->appointmentService->updateAppointment(
            $appointment,
            $request->validated(),
            $user instanceof User ? $user : null,
        );

        return new AppointmentResource($this->freshAppointment($appointment));
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully.',
        ]);
    }

    public function cancel(Appointment $appointment): AppointmentResource
    {
        $user = request()->user();

        return new AppointmentResource($this->freshAppointment(
            $this->appointmentService->cancelAppointment($appointment, $user instanceof User ? $user : null)
        ));
    }

    public function complete(Appointment $appointment): AppointmentResource
    {
        $user = request()->user();

        return new AppointmentResource($this->freshAppointment(
            $this->appointmentService->completeAppointment($appointment, $user instanceof User ? $user : null)
        ));
    }

    public function markNoShow(Appointment $appointment): AppointmentResource
    {
        $user = request()->user();

        return new AppointmentResource($this->freshAppointment(
            $this->appointmentService->markNoShow($appointment, $user instanceof User ? $user : null)
        ));
    }

    private function freshAppointment(Appointment $appointment): Appointment
    {
        return $appointment->refresh()->load([
            'property',
            'client',
            'owner',
            'provider',
            'contract',
            'mandate',
            'complaint',
            'collaboration',
            'creator',
        ]);
    }
}
