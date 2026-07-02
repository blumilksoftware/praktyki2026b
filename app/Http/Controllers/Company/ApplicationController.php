<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $company = Auth::user()->company;
        $offerId = $request->query("offer");
        $status = $request->query("status");

        $applications = $company->applications()
            ->when($offerId, fn(Builder $query) => $query->where("offer_id", $offerId))
            ->when($status, fn(Builder $query) => $query->where("status", $status))
            ->with(["student", "offer"])
            ->orderBy("created_at", "desc")
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Application $app) => [
                "id" => $app->id,
                "student_name" => $app->student->fullName(),
                "university" => $app->student->university,
                "application_date" => $app->created_at->toIso8601String(),
                "status" => $app->status->value,
                "offer_title" => $app->offer->title,
                "cv_url" => $app->cv_path ? route("company.applications.cv", $app) : null,
            ]);

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
            abort(404);
        }

        $disk = config("filesystems.default", "local");

        if (!Storage::disk($disk)->exists($application->cv_path)) {
            abort(404);
        }

        $extension = pathinfo($application->cv_path, PATHINFO_EXTENSION) ?: "pdf";
        $name = str_replace(" ", "_", $application->student->fullName());
        $filename = ($name ?: "student") . "_CV." . $extension;

        return Storage::disk($disk)->download($application->cv_path, $filename);
    }
}
