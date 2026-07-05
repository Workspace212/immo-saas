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
use App\Support\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppointmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly AppointmentService $appointmentService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Appointment::class);

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

        $this->applyIndexAuthorization($query, $request);

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
        $this->authorize('create', Appointment::class);

        $user = $request->user();
        $appointment = $this->appointmentService->createAppointment(
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return (new AppointmentResource($this->freshAppointment($appointment)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Appointment $appointment): AppointmentResource
    {
        $this->authorize('view', $appointment);

        return new AppointmentResource($this->freshAppointment($appointment));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): AppointmentResource
    {
        $this->authorize('update', $appointment);

        $user = $request->user();
        $appointment = $this->appointmentService->updateAppointment(
            $appointment,
            $this->tenantData($request->validated(), $user),
            $user instanceof User ? $user : null,
        );

        return new AppointmentResource($this->freshAppointment($appointment));
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment deleted successfully.',
        ]);
    }

    public function cancel(Appointment $appointment): AppointmentResource
    {
        $this->authorize('cancel', $appointment);

        $user = request()->user();

        return new AppointmentResource($this->freshAppointment(
            $this->appointmentService->cancelAppointment($appointment, $user instanceof User ? $user : null)
        ));
    }

    public function complete(Appointment $appointment): AppointmentResource
    {
        $this->authorize('complete', $appointment);

        $user = request()->user();

        return new AppointmentResource($this->freshAppointment(
            $this->appointmentService->completeAppointment($appointment, $user instanceof User ? $user : null)
        ));
    }

    public function markNoShow(Appointment $appointment): AppointmentResource
    {
        $this->authorize('markNoShow', $appointment);

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

    private function applyIndexAuthorization($query, Request $request): void
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401, 'Unauthenticated.');

        if ($user->hasAnyRole(['manager', 'assistant'])) {
            return;
        }

        if ($user->hasRole('agent')) {
            $userId = (int) $user->getKey();

            $query->where(function ($builder) use ($userId): void {
                $builder->where('created_by', $userId)
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('appointment_participants')
                            ->whereColumn('appointment_participants.appointment_id', 'appointments.id')
                            ->where('appointment_participants.user_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('contracts')
                            ->whereColumn('contracts.id', 'appointments.contract_id')
                            ->where('contracts.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('rental_units')
                            ->whereColumn('rental_units.contract_id', 'appointments.contract_id')
                            ->where('rental_units.assigned_agent_id', $userId);
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('complaints')
                            ->whereColumn('complaints.id', 'appointments.complaint_id')
                            ->where(function ($complaintQuery) use ($userId): void {
                                $complaintQuery->where('complaints.assigned_to', $userId)
                                    ->orWhere('complaints.created_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('properties')
                            ->whereColumn('properties.id', 'appointments.property_id')
                            ->where(function ($propertyQuery) use ($userId): void {
                                $propertyQuery->where('properties.created_by', $userId)
                                    ->orWhere('properties.updated_by', $userId);
                            });
                    })
                    ->orWhereExists(function ($subquery) use ($userId): void {
                        $subquery->selectRaw('1')
                            ->from('collaborations')
                            ->whereColumn('collaborations.id', 'appointments.collaboration_id')
                            ->where(function ($collaborationQuery) use ($userId): void {
                                $collaborationQuery->where('collaborations.requesting_agent_id', $userId)
                                    ->orWhere('collaborations.owner_agent_id', $userId)
                                    ->orWhere('collaborations.created_by', $userId);
                            });
                    });
            });

            return;
        }

        // TODO: employees and portal users need explicit supported appointment relationships before listing appointments.
        $query->whereRaw('1 = 0');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function tenantData(array $data, mixed $user): array
    {
        $agencyId = app(TenantContext::class)->agencyId();

        if ($agencyId === null && $user instanceof User) {
            $agencyId = $user->agency_id === null ? null : (int) $user->agency_id;
        }

        if ($agencyId !== null) {
            $data['agency_id'] = $agencyId;
        }

        return $data;
    }
}
