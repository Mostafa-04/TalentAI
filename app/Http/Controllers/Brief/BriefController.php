<?php

namespace App\Http\Controllers;

use App\Models\Brief;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BriefController extends Controller
{

    private function rules(): array
    {
        return [
            'title'                        => 'required|string|max:255',
            'sector'                       => 'nullable|string|max:255',
            'contract_type'                => ['nullable', Rule::in(['CDI', 'CDD', 'Freelance', 'Stage'])],
            'location'                     => 'nullable|string|max:255',
            'salary_range'                 => 'nullable|string|max:255',
            'min_experience_years'         => 'nullable|integer|min:0',
            'education_level'              => 'nullable|string|max:255',
            'gender_pref'                  => ['nullable', Rule::in(['M', 'F', 'any'])],
            'age_range'                    => 'nullable|string|max:50',
            'mission_description'          => 'nullable|string',
            'required_skills'              => 'nullable|string',
            'soft_skills'                  => 'nullable|string',
            'scoring_weights'              => 'nullable|array',
            'scoring_weights.experience'   => 'required_with:scoring_weights|integer|min:0|max:100',
            'scoring_weights.education'    => 'required_with:scoring_weights|integer|min:0|max:100',
            'scoring_weights.sector'       => 'required_with:scoring_weights|integer|min:0|max:100',
            'scoring_weights.soft_skills'  => 'required_with:scoring_weights|integer|min:0|max:100',
            'scoring_weights.location'     => 'required_with:scoring_weights|integer|min:0|max:100',
            'status'                       => ['required', Rule::enum(BriefStatus::class)],
        ];
    }

    /**
     * Display a paginated list of briefs, optionally filtered by search term and status.
     */
    public function index(Request $request)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $briefs = Brief::with('creator')
                ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%$s%"))
                ->when($request->status, fn($q, $s) => $q->where('status', $s))
                ->latest()
                ->paginate(10)
                ->through(function ($brief) {
                    return [
                        'id'           => $brief->id,
                        'title'        => $brief->title,
                        'sector'       => $brief->sector,
                        'contract_type'=> $brief->contract_type,
                        'location'     => $brief->location,
                        'status'       => $brief->status,
                        'created_by'   => $brief->creator?->name,
                        'created_at'   => $brief->created_at->toDateTimeString(),
                    ];
                });

            $logger->log(
                'brief.index',
                'Consultation de la liste des briefs.',
                ['filters' => $request->only(['search', 'status'])],
                [Brief::class]
            );

            return Inertia::render('Briefs/Index', [
                'briefs'  => $briefs,
                'filters' => $request->only(['search', 'status']),
            ]);
        } catch (\Throwable $e) {
            $logger->log(
                'brief.index.error',
                'Erreur lors de la récupération de la liste des briefs : ' . $e->getMessage(),
                ['exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Show the form for creating a new brief.
     */
    public function create()
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $logger->log(
                'brief.create',
                'Affichage du formulaire de création d\'un brief.',
                [],
                [Brief::class]
            );

            return Inertia::render('Briefs/Create');
        } catch (\Throwable $e) {
            $logger->log(
                'brief.create.error',
                'Erreur lors de l\'affichage du formulaire de création : ' . $e->getMessage(),
                ['exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Validate and persist a newly created brief.
     */
    public function store(Request $request)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $validated = $request->validate($this->rules());

            $brief = new Brief($validated);
            $brief->created_by = auth()->id();
            $brief->save();

            $logger->log(
                'brief.store',
                "Création du brief « {$brief->title} » (ID : {$brief->id}).",
                ['brief_id' => $brief->id, 'title' => $brief->title],
                [Brief::class]
            );

            return redirect()->route('briefs.index')
                ->with('success', 'Brief créé avec succès.');
        } catch (\Throwable $e) {
            $logger->log(
                'brief.store.error',
                'Erreur lors de la création du brief : ' . $e->getMessage(),
                ['exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Display the specified brief.
     */
    public function show(Brief $brief)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $logger->log(
                'brief.show',
                "Consultation du brief « {$brief->title} » (ID : {$brief->id}).",
                ['brief_id' => $brief->id],
                [Brief::class]
            );

            return Inertia::render('Briefs/Show', [
                'brief' => $brief,
            ]);
        } catch (\Throwable $e) {
            $logger->log(
                'brief.show.error',
                "Erreur lors de la consultation du brief (ID : {$brief->id}) : " . $e->getMessage(),
                ['brief_id' => $brief->id, 'exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Show the form for editing the specified brief.
     */
    public function edit(Brief $brief)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $logger->log(
                'brief.edit',
                "Affichage du formulaire d'édition du brief « {$brief->title} » (ID : {$brief->id}).",
                ['brief_id' => $brief->id],
                [Brief::class]
            );

            return Inertia::render('Briefs/Edit', [
                'brief' => $brief,
            ]);
        } catch (\Throwable $e) {
            $logger->log(
                'brief.edit.error',
                "Erreur lors de l'affichage du formulaire d'édition (ID : {$brief->id}) : " . $e->getMessage(),
                ['brief_id' => $brief->id, 'exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Validate and apply updates to the specified brief, logging each modified field.
     */
    public function update(Request $request, Brief $brief)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $validated = $request->validate($this->rules());

            $modifications = collect($validated)
                ->filter(fn($newValue, $key) => $brief->getAttribute($key) != $newValue)
                ->map(fn($newValue, $key) => [
                    'avant'  => $brief->getAttribute($key),
                    'après'  => $newValue,
                ])
                ->toArray();

            $statutAvant = $brief->status;
            $brief->update($validated);

            $champsModifiés   = implode(', ', array_keys($modifications));
            $descriptionBase  = "Mise à jour du brief « {$brief->title} » (ID : {$brief->id}).";
            $descriptionDetail = count($modifications)
                ? " Champs modifiés : {$champsModifiés}."
                : ' Aucune modification détectée.';

            $transitionStatut = $statutAvant !== $brief->status
                ? " Changement de statut : « {$statutAvant} » → « {$brief->status} »."
                : '';

            $logger->log(
                'brief.update',
                $descriptionBase . $descriptionDetail . $transitionStatut,
                [
                    'brief_id'      => $brief->id,
                    'modifications' => $modifications,
                    'statut_avant'  => $statutAvant,
                    'statut_après'  => $brief->status,
                ],
                [Brief::class]
            );

            return redirect()->route('briefs.index')
                ->with('success', 'Brief mis à jour avec succès.');
        } catch (\Throwable $e) {
            $logger->log(
                'brief.update.error',
                "Erreur lors de la mise à jour du brief (ID : {$brief->id}) : " . $e->getMessage(),
                ['brief_id' => $brief->id, 'exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }

    /**
     * Remove the specified brief from storage.
     */
    public function destroy(Brief $brief)
    {
        /** @var ActivityLogger $logger */
        $logger = app(ActivityLogger::class);

        try {
            $briefId    = $brief->id;
            $briefTitle = $brief->title;

            $brief->delete();

            $logger->log(
                'brief.destroy',
                "Suppression du brief « {$briefTitle} » (ID : {$briefId}).",
                ['brief_id' => $briefId, 'title' => $briefTitle],
                [Brief::class]
            );

            return redirect()->route('briefs.index')
                ->with('success', 'Brief supprimé avec succès.');
        } catch (\Throwable $e) {
            $logger->log(
                'brief.destroy.error',
                "Erreur lors de la suppression du brief (ID : {$brief->id}) : " . $e->getMessage(),
                ['brief_id' => $brief->id, 'exception' => $e->getMessage()],
                [Brief::class]
            );

            throw $e;
        }
    }
}