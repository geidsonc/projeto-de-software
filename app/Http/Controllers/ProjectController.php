<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   $projects_query = Project::query()->latest();

        if (auth()->user()->type != User::TYPE_GERENTE) {
            $projects_query = auth()->user()->projects();
        }

        $projects = $projects_query->with([
            'users',
            'projectStatus' => function($query) {
                $query->latest();
            }
        ])->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (auth()->user()->type != User::TYPE_GERENTE) {
            return abort(401, 'Não autorizado');
        }

        $request->validate([
            'name' => 'required',
            'process_number' => 'required|unique:projects|max:20',
            'agreement_number' => 'required|unique:projects|max:20',
            'action' => 'required|in:MSD,MH,SAA,SES,RES,DRE',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'city' => 'required',
            'users_ids' => 'array',
        ]);

        $project = Project::create($request->except(['users_ids']));
        $project->projectStatus()->create([
            'user_id' => auth()->id(),
            'technical_opinion' => 'Projeto criado',
            'status' => $request->status,
        ]);

        $this->insertResponsibles($project, $request->users_ids);

        return true;
    }

    private function insertResponsibles($project, $users_ids)
    {
        $project->users()->sync($users_ids);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $project->action_value = $project->translateAction();
        $project->loadMissing([
            'users',
            'projectStatus.user'
        ]);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        if (auth()->user()->type != User::TYPE_GERENTE) {
            return abort(401, 'Não autorizado');
        }

        $request->validate([
            'name' => 'required',
            'process_number' => 'required|max:20',
            'agreement_number' => 'required|max:20',
            'action' => 'required|in:MSD,MH,SAA,SES,RES,DRE',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'city',
            'resume',
            'users_ids' => 'array',
        ]);

        $project->update($request->except(['users_ids']));
        $project->users()->sync($request->users_ids);

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if (auth()->user()->type != User::TYPE_GERENTE) {
            return abort(401, 'Não autorizado');
        }

        return $project->delete();
    }
}
