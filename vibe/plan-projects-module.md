# Plan: Módulo de Proyectos CRM

## Contexto

El usuario quiere un módulo de gestión de proyectos integrado en su instalación de Relaticle CRM. Los proyectos se asocian a empresas (Company) y contactos (People), con soporte de notas, campos personalizados, y todos los patrones estándar del sistema (multi-tenant, ULID, soft deletes, policy, actions, tests).

Las tareas enriquecidas se dejan para una segunda fase.

---

## Archivos a CREAR

### 1. Migración principal — `database/migrations/{ts}_create_projects_table.php`
Campos:
- `id` ULID primary key
- `team_id` FK → teams (cascade delete)
- `creator_id` FK → users (nullable, set null)
- `company_id` FK → companies (nullable, set null)
- `account_owner_id` FK → users (nullable, set null)
- `name` string
- `slug` string
- `description` text nullable
- `status` string (enum ProjectStatus)
- `budget` integer nullable (céntimos)
- `color` string nullable
- `start_date` date nullable
- `end_date` date nullable
- `due_date` date nullable
- `creation_source` string (50)
- `order_column` unsignedBigInteger nullable
- `timestamps`, `softDeletes`
- Índice compuesto: `idx_projects_team_activity` (team_id, deleted_at, creation_source, created_at)

### 2. Migración pivot — `database/migrations/{ts}_create_project_people_table.php`
Tabla `project_people`: `project_id` (ULID FK) + `person_id` (ULID FK → people)

### 3. Enum — `app/Enums/ProjectStatus.php`
`Planning | Active | OnHold | Completed | Cancelled`
Implementa `HasColor` y `HasLabel` (igual que `CreationSource`).

### 4. Modelo — `app/Models/Project.php`
Traits: `BelongsToTeamCreator`, `HasCreator`, `HasFactory`, `HasNotes`, `HasTeam`, `HasUlids`, `SoftDeletes`, `UsesCustomFields`, `HasSlug` (Spatie)
Interfaces: `HasCustomFields`
Relaciones:
- `company()` → `BelongsTo<Company>`
- `accountOwner()` → `BelongsTo<User>`
- `people()` → `BelongsToMany<People>` via `project_people` (person_id)
Fillable: `name`, `slug`, `description`, `status`, `budget`, `color`, `start_date`, `end_date`, `due_date`, `creation_source`, `company_id`, `account_owner_id`
`getSlugOptions()`: igual que `Team::getSlugOptions()`, generado desde `name`.

### 5. Factory — `database/factories/ProjectFactory.php`
Patrón idéntico a `CompanyFactory`: name faker, company_id, team_id, sequence con timestamps.

### 6. Policy — `app/Policies/ProjectPolicy.php`
Patrón idéntico a `CompanyPolicy` (viewAny/view/create/update/delete/deleteAny/restore/restoreAny/forceDelete/forceDeleteAny).

### 7. Actions
- `app/Actions/Project/CreateProject.php` — Patrón idéntico a `CreateCompany` (Arr::only, DB::transaction, abort_unless can('create'))
- `app/Actions/Project/UpdateProject.php` — Patrón idéntico a `UpdateCompany`
- `app/Actions/Project/DeleteProject.php` — Patrón idéntico a `DeleteCompany`

### 8. Recurso Filament — `app/Filament/Resources/ProjectResource.php`
- Navigation group: `Workspace` (mismo que Opportunity), `$navigationSort = 4`
- Icon: `heroicon-o-briefcase`
- Form: `name` (required, live onBlur → auto-slug), `slug`, `company_id` (Select relationship), `status` (Select enum), `description` (Textarea), `budget` (TextInput numeric), `color` (ColorPicker), `start_date`, `end_date`, `due_date`, `account_owner_id`, `CustomFields::form()`
- Table: `name` (searchable), `company.name` (searchable), `status` (badge), `due_date`, `accountOwner.name`, `creator.name`, `created_at`
- Filters: `SelectFilter status`, `SelectFilter creation_source`, `TrashedFilter`
- `getEloquentQuery()` con `with(['team', 'customFieldValues.customField.options'])` y `withoutGlobalScopes([SoftDeletingScope::class])`

### 9. Páginas del recurso
- `app/Filament/Resources/ProjectResource/Pages/ListProjects.php` — Extiende `ListRecords`, CreateAction en header
- `app/Filament/Resources/ProjectResource/Pages/ViewProject.php` — Extiende `ViewRecord`, infolist con name, status, company, dates, budget, color; RelationManagers: People, Notes

### 10. RelationManagers del ProjectResource
- `app/Filament/Resources/ProjectResource/RelationManagers/NotesRelationManager.php` — Patrón idéntico al de `CompanyResource`
- `app/Filament/Resources/ProjectResource/RelationManagers/PeopleRelationManager.php` — Patrón idéntico al de `CompanyResource`

### 11. RelationManager para CompanyResource
- `app/Filament/Resources/CompanyResource/RelationManagers/ProjectsRelationManager.php`
  Muestra proyectos desde la vista de empresa: nombre, status (badge), due_date, accountOwner.name

### 12. Traducciones
- `lang/en/filament/resources/project.php`
- `lang/es/filament/resources/project.php`
- `lang/fr/filament/resources/project.php`

### 13. Tests — `tests/Feature/Filament/App/Resources/ProjectResourceTest.php`
Patrón idéntico a `CompanyResourceTest`: renderizado, columnas, búsqueda, CRUD, validación, autorización, observer (creator_id + team_id).

---

## Archivos a MODIFICAR

### `app/Http/Middleware/ApplyTenantScopes.php`
Añadir `Project::addGlobalScope(new TeamScope);` junto al resto de modelos.

### `app/Models/Company.php`
Añadir relación `projects(): HasMany<Project, $this>`.

### `app/Models/Note.php`
1. Añadir `projects(): MorphToMany<Project, $this>` → `morphedByMany(Project::class, 'noteable')`
2. Actualizar `forNotableType()` scope para incluir `'project' => 'projects'`
3. Actualizar `forNotableId()` scope para incluir `->orWhereHas('projects', ...)`

### `app/Filament/Resources/CompanyResource/Pages/ViewCompany.php`
Añadir `ProjectsRelationManager::class` al array de `getRelationManagers()`.

---

## Verificación

1. `php artisan migrate` — aplica las dos migraciones nuevas sin errores
2. `php artisan test --filter=ProjectResource` — todos los tests pasan
3. Abrir la app en el navegador → verificar que aparece "Projects" en la navegación bajo "Workspace"
4. Crear un proyecto vinculado a una empresa → verificar que aparece en la pestaña Projects de la vista de empresa
5. `vendor/bin/phpstan analyse` — sin nuevos errores
6. `composer test:type-coverage` — cobertura ≥ 99.9%
