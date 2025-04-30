<?php

namespace App\Http\Controllers;

use App\Enums\SystemActionType;
use App\Services\SystemActionService;
use Illuminate\Http\Request;
use App\Models\Symptom;
use App\Models\UserSymptom;
use Illuminate\Support\Facades\Validator;

class SymptomController extends Controller
{

    protected SystemActionService $systemActionService;

    public function __construct(SystemActionService $systemActionService)
    {
        $this->systemActionService = $systemActionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function common()
    {
        //
        $symptoms = Symptom::where('user_id', null)->get();
        return response()->json($symptoms);
    }

    public function index()
    {
        $userId = auth()->id();
        $symptoms = Symptom::where('user_id', $userId)->get();
        $commonSymptoms = Symptom::where('user_id', null)->get();
        
        $this->systemActionService->logAction(SystemActionType::SYMPTOMPS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json(
            $symptoms->merge($commonSymptoms)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'color' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = auth()->user();
        $symptom = Symptom::create([
            'name' => $request->name,
            'color' => $request->color,
            'user_id' => $user->id
        ]);

        $this->systemActionService->logAction(SystemActionType::SYMPTOMPS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json($symptom);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        $symptom = Symptom::where('id', $id)->where('user_id', $userId)->first();

        if (!$symptom) {
            return response()->json(['error' => 'Symptom not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'color' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $symptom->name = $request->name;
        $symptom->color = $request->color;
        $symptom->save();

        $this->systemActionService->logAction(SystemActionType::SYMPTOMPS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json($symptom);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userId = auth()->id();


        $symptom = Symptom::where('id', $id)->where('user_id', $userId)->first();

        if (!$symptom) {
            return response()->json(['error' => 'Symptom not found'], 404);
        }

        UserSymptom::where('symptom_id', $id)->delete();

        $symptom->delete();

        $this->systemActionService->logAction(SystemActionType::SYMPTOMPS_INTERACTION, [
            'user_id' => auth()->id()
        ]);

        return response()->json(['success'=>true, 'message' => 'Symptom deleted successfully']);
    }
}
