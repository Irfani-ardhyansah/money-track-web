<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
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

    public function test_transaction_index_page_loads(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertOk();
    }

    public function test_can_create_income_transaction(): void
    {
        $response = $this->post(route('transactions.store'), [
            'type' => 'income',
            'occurred_at' => '2026-05-01',
            'amount' => 5000000,
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type' => 'income',
            'amount' => 5000000,
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
        ]);
    }

    public function test_can_create_expense_transaction(): void
    {
        $this->post(route('transactions.store'), [
            'type' => 'expense',
            'occurred_at' => '2026-05-01',
            'amount' => 50000,
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'expense',
            'amount' => 50000,
            'wallet_id' => $this->wallet->id,
        ]);
    }

    public function test_transfer_sets_category_to_null(): void
    {
        $this->post(route('transactions.store'), [
            'type' => 'transfer',
            'occurred_at' => '2026-05-01',
            'amount' => 100000,
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet2->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'transfer',
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet2->id,
            'category_id' => null,
        ]);
    }

    public function test_transfer_requires_different_wallets(): void
    {
        $response = $this->post(route('transactions.store'), [
            'type' => 'transfer',
            'occurred_at' => '2026-05-01',
            'amount' => 100000,
            'wallet_id' => $this->wallet->id,
            'to_wallet_id' => $this->wallet->id, // same wallet — invalid
        ]);

        $response->assertSessionHasErrors('to_wallet_id');
    }

    public function test_can_delete_transaction(): void
    {
        $tx = Transaction::create([
            'type' => 'expense',
            'occurred_at' => '2026-05-01',
            'amount' => 50000,
            'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $this->delete(route('transactions.destroy', $tx))
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
    }

    public function test_transaction_index_filters_by_type(): void
    {
        Transaction::create([
            'type' => 'income', 'occurred_at' => '2026-05-01',
            'amount' => 1000, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->incomeCategory->id,
        ]);
        Transaction::create([
            'type' => 'expense', 'occurred_at' => '2026-05-01',
            'amount' => 500, 'wallet_id' => $this->wallet->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $response = $this->get(route('transactions.index', ['type' => 'income']));
        $response->assertOk();
        $response->assertSeeText('Gaji');
        $response->assertDontSeeText('Makan');
    }
}
