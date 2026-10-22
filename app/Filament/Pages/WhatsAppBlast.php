<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MessageTemplate;
use App\Models\Occupant;
use App\Models\WhatsappLog;
use App\Services\WhatsAppService;
use App\Support\NavigationGroups;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class WhatsAppBlast extends Page implements HasForms, HasTable
{
    use AuthorizesPageAccess;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $permission = 'website.manage';

    protected static ?string $slug = 'whatsapp-blast';

    protected string $view = 'filament.pages.whatsapp-blast';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 20;

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::SETTINGS;
    }

    public static function getNavigationLabel(): string
    {
        return 'WhatsApp Blast';
    }

    public function getTitle(): string
    {
        return 'WhatsApp Blast';
    }

    public function mount(): void
    {
        $this->form->fill(['target' => 'all_active']);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('target')
                ->label('Target Penerima')
                ->options([
                    'all_active' => 'Semua penyewa aktif (punya kontrak aktif)',
                    'overdue' => 'Penyewa dengan tagihan jatuh tempo',
                    'expiring' => 'Kontrak berakhir ≤ 30 hari',
                    'manual' => 'Pilih manual',
                ])
                ->required()
                ->live(),
            Select::make('occupant_ids')
                ->label('Penyewa (manual)')
                ->multiple()
                ->searchable()
                ->options(fn () => Occupant::orderBy('name')->pluck('name', 'id'))
                ->visible(fn ($get) => $get('target') === 'manual'),
            Select::make('template_id')
                ->label('Template Pesan (opsional)')
                ->searchable()
                ->options(fn () => MessageTemplate::orderBy('name')->pluck('name', 'id'))
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('message',
                    $state ? (string) MessageTemplate::find($state)?->content : '')),
            Textarea::make('message')
                ->label('Isi Pesan')
                ->rows(6)
                ->required()
                ->maxLength(1000)
                ->helperText('Bisa pakai {nama}, {kamar}, {tagihan} — otomatis diganti per penerima.'),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Kirim Blast')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(fn () => $this->send()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(WhatsappLog::query()->where('type', 'blast')->latest())
            ->columns([
                TextColumn::make('to_name')->label('Penerima')->searchable(),
                TextColumn::make('to_number')->label('Nomor'),
                TextColumn::make('message')->label('Pesan')->limit(60),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
            ])
            ->paginated([10, 25]);
    }

    public function send(): void
    {
        $state = $this->form->getState();
        $occupants = $this->resolveRecipients($state['target'], $state['occupant_ids'] ?? []);

        if ($occupants->isEmpty()) {
            Notification::make()->title('Tidak ada penerima yang cocok.')->danger()->send();

            return;
        }

        $service = app(WhatsAppService::class);
        $sent = 0;
        foreach ($occupants as $occupant) {
            $message = $this->personalize($state['message'], $occupant);
            if ($service->send($occupant->whatsapp_number, $message, 'blast', $occupant)) {
                $sent++;
            }
        }

        Notification::make()
            ->title("Blast selesai: {$sent} dari {$occupants->count()} terkirim.")
            ->body($sent < $occupants->count() ? 'Sebagian gagal / gateway nonaktif — cek log WhatsApp.' : null)
            ->success()
            ->send();

        $this->resetTable();
    }

    /** @return \Illuminate\Support\Collection<int, Occupant> */
    private function resolveRecipients(string $target, array $manualIds = [])
    {
        return match ($target) {
            'manual' => Occupant::whereIn('id', $manualIds)->get(),
            'overdue' => Occupant::whereHas('leases.invoices',
                fn ($q) => $q->where('status', 'overdue'))->get(),
            'expiring' => Occupant::whereHas('leases',
                fn ($q) => $q->where('status', 'active')
                    ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()]))->get(),
            default => Occupant::whereHas('leases',
                fn ($q) => $q->where('status', 'active'))->get(),
        };
    }

    private function personalize(string $message, Occupant $occupant): string
    {
        $lease = Lease::where('occupant_id', $occupant->id)
            ->where('status', 'active')->with('room')->first();
        $overdue = Invoice::whereHas('lease', fn ($q) => $q->where('occupant_id', $occupant->id))
            ->where('status', 'overdue')->sum('total');

        return strtr($message, [
            '{nama}' => $occupant->name,
            '{kamar}' => $lease?->room?->room_number ?? '-',
            '{tagihan}' => 'Rp '.number_format((float) $overdue, 0, ',', '.'),
        ]);
    }
}
