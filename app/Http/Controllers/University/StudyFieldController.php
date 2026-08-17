<?php

declare(strict_types=1);

namespace App\Http\Controllers\University;

use App\Actions\University\CreateStudyField;
use App\Actions\University\DeleteStudyField;
use App\Actions\University\RenameStudyField;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteStudyFieldRequest;
use App\Http\Requests\StoreStudyFieldRequest;
use App\Http\Requests\UpdateStudyFieldRequest;
use App\Models\Faculty;
use App\Models\StudyField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StudyFieldController extends Controller
{
    public function __construct(
        private readonly CreateStudyField $createStudyField,
        private readonly RenameStudyField $renameStudyField,
        private readonly DeleteStudyField $deleteStudyField,
    ) {}

    public function store(StoreStudyFieldRequest $request, Faculty $faculty): RedirectResponse
    {
        Gate::authorize("create", [StudyField::class, $faculty]);

        $this->createStudyField->execute($faculty, $request->string("name")->toString());

        return back();
    }

    public function update(UpdateStudyFieldRequest $request, StudyField $studyField): RedirectResponse
    {
        Gate::authorize("update", $studyField);

        $this->renameStudyField->execute($studyField, $request->string("name")->toString());

        return back();
    }

    public function destroy(DeleteStudyFieldRequest $request, StudyField $studyField): RedirectResponse
    {
        Gate::authorize("delete", $studyField);

        $reassignTo = $request->filled("reassign_to")
            ? StudyField::findOrFail($request->string("reassign_to")->toString())
            : null;

        $this->deleteStudyField->execute($studyField, $reassignTo);

        return back();
    }
}
