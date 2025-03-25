@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-success">
        Import réalisé avec succès !
    </div>

    <h3>Données importées :</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Projets</div>
                <div class="card-body">
                    @foreach(DB::table('temp_projects')->get() as $project)
                        <div>{{ $project->project_title }} ({{ $project->client_name }})</div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Afficher les autres tables de la même manière -->
        </div>
    </div>
</div>
@endsection