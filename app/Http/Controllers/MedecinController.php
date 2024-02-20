<?php

namespace App\Http\Controllers;

use App\Http\Requests\DossierMedicalRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\RendezVousResource;
use App\Models\DossierMedical;
use App\Models\Patient;
use App\Models\RendezVous;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedecinController extends Controller
{


    public function listePatient()
    {
        $today = date('Y-m-d');
        $rendezVous = RendezVous::with(['medecin', 'patient'])
            ->whereDate('dateRend', $today)
            ->orderBy('heureRend')
            ->get();
        return response()->json([
            "data" => [
                "patients" => RendezVousResource::collection($rendezVous)
            ]
        ]);
    }

    public function nbrePatientParJour()
    {
        $user = Auth::user();
        if ($user->role === "medecin") {
            $medecin_id = $user->id;
            //dd($medecin_id);
            $nbRendezVous = RendezVous::where('medecin_id', $medecin_id)->count();
            return response()->json([
                "data" => [
                    "nbRendezVous" => $nbRendezVous
                ]
            ]);
        }
    }
    public function generateNumero()
    {
        return rand(10000, 99999);
    }
    public function createDossierMedical(DossierMedicalRequest $request)
    {
        $numero = $this->generateNumero();
        while (DossierMedical::where('numero', $numero)->exists()) {
            $numero = $this->generateNumero();
        }
        // Créer le dossier
        $dossier = DossierMedical::create([
            "dateEntre" => $request->dateEntre,
            "symptomes" => $request->symptomes,
            "maladie_antecedent" => $request->maladie_antecedent,
            "bilan" => $request->bilan,
            "numero" => $numero,
        ]);
        return response()->json([
            "data" => [
                "message" => "Insertion réuissie",
                "status" => 200,
                "dossier" => $dossier
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $patient = Patient::where('numeroDossier', $id)->first();
        //$patient = Patient::where("dossier_medical_id", $dossier->id)->first();
        return response()->json([
            "data" => [
                "status" => 200,
                "patient2" => new PatientResource($patient),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DossierMedicalRequest $request, string $id)
    {
        $dossier = DossierMedical::find($id);
        $dossier->update([
            "dateEntre" => $request->dateEntre,
            "symptomes" => $request->symptomes,
            "maladie_antecedent" => $request->maladie_antecedent,
            "bilan" => $request->bilan,
        ]);
        return response()->json([
            "data" => [
                "status" => 200,
                "message" => "modification avec succès",
                "dosseir" => $dossier
            ]
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dossier = DossierMedical::find($id);
        $dossier->delete();
        return response()->json(
            [
                "data" => [
                    'message' => 'User supprimé avec succès',
                    'status' => 200
                ]
            ]
        );
    }
}
