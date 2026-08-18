<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\UpdateApplicationStatusAction;
use App\Actions\Notification\MarkAllNotificationsAsReadAction;
use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly UpdateApplicationStatusAction $updateApplicationStatusAction,
        private readonly MarkAllNotificationsAsReadAction $markAllNotificationsAsReadAction,
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        $user = Auth::user();
        $company = $user->company;
        $offerId = $request->query("offer");
        $status = $request->query("status");

        if ($status === ApplicationStatus::Pending->value || $status === "pending") {
            $this->markAllNotificationsAsReadAction->execute($user);
        }

        $applications = $company->applications()
            ->when($offerId, fn(Builder $query): Builder => $query->where("offer_id", $offerId))
            ->when($status, fn(Builder $query): Builder => $query->where("applications.status", $status))
            ->with(["student", "offer"])
            ->orderBy("created_at", "desc")
            ->paginate(6)
            ->withQueryString()
            ->through(fn(Application $app): array => [
                "id" => $app->id,
                "student_name" => $app->student->fullName(),
                "university" => $app->student->university,
                "application_date" => $app->created_at->toIso8601String(),
                "status" => $app->status->value,
                "offer_title" => $app->offer->title,
                "cv_url" => $app->cv_path ? route("company.applications.cv", $app) : null,
                "profile_url" => route("company.students.show", $app->student),
            ]);

        if ($request->wantsJson()) {
            return response()->json($applications);
        }

        $offers = $company->offers()
            ->select("id", "title")
            ->orderBy("title")
            ->get();

        return Inertia::render("Company/Applications", [
            "applications" => $applications,
            "offers" => $offers,
            "filters" => [
                "offer" => $offerId,
                "status" => $status,
            ],
        ]);
    }

    public function downloadCv(Application $application): StreamedResponse
    {
        Gate::authorize("downloadCv", $application);

        if (!$application->cv_path) {
            throw new NotFoundHttpException();
        }

        $disk = config("filesystems.default", "local");

        if (!Storage::disk($disk)->exists($application->cv_path)) {
            throw new NotFoundHttpException();
        }

        $extension = pathinfo($application->cv_path, PATHINFO_EXTENSION) ?: "pdf";
        $name = str_replace(" ", "_", $application->student->fullName());
        $filename = ($name ?: "student") . "_CV." . $extension;

        return Storage::disk($disk)->download($application->cv_path, $filename);
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, Application $application): RedirectResponse
    {
        Gate::authorize("updateStatus", $application);

        $this->updateApplicationStatusAction->execute(
            $application,
            ApplicationStatus::from($request->validated("status")),
        );

        return back();
    }
}
