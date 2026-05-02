<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ReportService;
use App\Services\WalletBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private Wallet $wallet;

    private Wallet $wallet2;

    private Category $incomeCategory;

    private Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->wallet = Wallet::create(['name' => 'BCA', 'type' => 'bank']);
        $this->wallet2 = Wallet::create(['name' => 'Cash', 'type' => 'cash']);
        $this->incomeCategory = Category::create(['name' => 'Gaji', 'type' => 'income']);
        $this->expenseCategory = Category::create(['name' => 'Makan', 'type' => 'expense']);
    }

    public function test_monthly_summary_excludes_transfers(): void
    {
        // Income: 5_000_000
        Transaction::create([
            'type' => 'income', 'occurred_at' => '2026-05-10',
            'amount' => 5_000_000, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
        ]);
        // Expense: 200_000
        Transaction::create([
            'type' => 'expense', 'occurred_at' => '2026-05-11',
            'amount' => 200_000, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);
        // Transfer: should NOT affect income/expense totals
        Transaction::create([
            'type' => 'transfer', 'occurred_at' => '2026-05-12',
            'amount' => 1_000_000,
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet2->id,
        ]);

        $service = app(ReportService::class);
        $summary = $service->monthlySummary(5, 2026);

        $this->assertEquals(5_000_000, $summary['income']);
        $this->assertEquals(200_000, $summary['expense']);
        $this->assertEquals(4_800_000, $summary['net']);
    }

    public function test_category_breakdown_excludes_transfers(): void
    {
        Transaction::create([
            'type' => 'expense', 'occurred_at' => '2026-05-11',
            'amount' => 100_000, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);
        // Transfer should not appear in category breakdown
        Transaction::create([
            'type' => 'transfer', 'occurred_at' => '2026-05-12',
            'amount' => 500_000,
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet2->id,
        ]);

        $service = app(ReportService::class);
        $breakdown = $service->categoryBreakdown(5, 2026);

        $this->assertCount(1, $breakdown);
        $this->assertEquals('Makan', $breakdown->first()->category_name);
        $this->assertEquals(100.0, $breakdown->first()->percentage);
    }

    public function test_wallet_balance_accounts_for_transfers(): void
    {
        // Wallet 1 starts at 0; income 5_000_000, then transfer 1_000_000 out
        Transaction::create([
            'type' => 'income', 'occurred_at' => '2026-05-01',
            'amount' => 5_000_000, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
        ]);
        Transaction::create([
            'type' => 'transfer', 'occurred_at' => '2026-05-02',
            'amount' => 1_000_000,
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet2->id,
        ]);

        $service = app(WalletBalanceService::class);
        $balances = $service->allBalances();

        $this->assertEquals(4_000_000, $balances->get($this->wallet->id));
        $this->assertEquals(1_000_000, $balances->get($this->wallet2->id));
    }

    public function test_dashboard_page_loads(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }
}
