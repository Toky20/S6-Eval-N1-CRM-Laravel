@extends('layouts.master')

@section('heading')
    {{ __('Import Data')}}
@stop
@section('content')
<div class="container">
    <h1>Importer des données</h1>
    
    <form method="POST" action="{{ route('import.process') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Fichier Projets (CSV)</label>
            <input type="file" class="form-control" name="projects" required>
            <small class="form-text text-muted">
                Colonnes attendues : project_title, client_name
            </small>
        </div>

        <div class="mb-3">
            <label class="form-label">Fichier Tâches (CSV)</label>
            <input type="file" class="form-control" name="tasks" required>
            <small class="form-text text-muted">
                Colonnes attendues : project_title, task_title
            </small>
        </div>

        <div class="mb-3">
            <label class="form-label">Fichier Leads/Invoices (CSV)</label>
            <input type="file" class="form-control" name="leads" required>
            <small class="form-text text-muted">
                Colonnes attendues : client_name, lead_title, type, produit, prix, quantite
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Importer</button>
    </form>

    {{-- @if($errors->any())
        <div class="alert alert-danger mt-4">
            <h4>Erreurs détectées :</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    {{-- @if(session('success'))
        <div class="alert alert-success mt-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mt-4">
            <h4>Erreurs détectées :</h4>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    {{-- @if(session('success'))
    <div class="alert alert-success">
        <h4>{{ session('success')['message'] }}</h4>
        <ul class="mb-0">
            <li>Projets importés : {{ session('success')['counts']['projects_count'] }}</li>
            <li>Tâches importées : {{ session('success')['counts']['tasks_count'] }}</li>
            <li>Transactions importées : {{ session('success')['counts']['leads_count'] }}</li>
        </ul>
    </div>
@endif --}}

{{-- @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif --}}
{{-- Messages et statistiques --}}
@if(session('success'))
<div class="alert alert-success">
    <h4>{{ session('success')['message'] }}</h4>
    <ul class="mb-0">
        <li>Projets importés : {{ session('success')['counts']['projects_count'] }}</li>
        <li>Tâches importées : {{ session('success')['counts']['tasks_count'] }}</li>
        <li>Transactions importées : {{ session('success')['counts']['leads_count'] }}</li>
    </ul>
</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

</div>
@endsection