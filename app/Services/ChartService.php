<?php

namespace App\Services;

use App\Repositories\Interfaces\WalletTransactionRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Services\Interfaces\ChartServiceInterface;
use App\Support\ServiceResult;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class ChartService implements ChartServiceInterface
{
    public function __construct(
        private WalletTransactionRepositoryInterface $transactionRepository,
        private WalletRepositoryInterface $walletRepository,
    ) {
    }

    public function getMonthlyExpenses(?string $month = null, ?string $walletId = null): ServiceResult
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return ServiceResult::error(__('messages.unauthenticated'), Response::HTTP_UNAUTHORIZED);
            }

            $targetMonth = $month ? Carbon::parse($month . '-01') : Carbon::now();
            
            if ($walletId) {
                $wallet = $this->walletRepository->findByUuid($walletId);
                if (!$wallet || $wallet->user_id !== $userId) {
                    return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
                }
            }

            $weeklyExpenses = $this->calculateWeeklyExpenses($userId, $targetMonth, $walletId);

            $data = [
                'month' => $targetMonth->format('Y-m'),
                'weekly_expenses' => $weeklyExpenses,
            ];

            return ServiceResult::success($data);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    private function calculateWeeklyExpenses(int $userId, Carbon $month, ?string $walletId): array
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $weeklyData = [];
        $weekNumber = 1;
        
        $current = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        
        while ($current->lessThanOrEqualTo($endOfMonth)) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);
            
            if ($weekStart->lessThan($startOfMonth)) {
                $weekStart = $startOfMonth->copy();
            }
            
            if ($weekEnd->greaterThan($endOfMonth)) {
                $weekEnd = $endOfMonth->copy();
            }
            
            if ($weekStart->month === $month->month) {
                $expenses = $this->transactionRepository->getExpensesByDateRange(
                    $userId,
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d'),
                    $walletId
                );

                $weeklyData[] = [
                    'week' => $weekNumber,
                    'date_range' => $weekStart->format('Y-m-d') . ' - ' . $weekEnd->format('Y-m-d'),
                    'total' => $expenses['total'],
                    'transaction_count' => $expenses['count'],
                ];
                
                $weekNumber++;
            }
            
            $current->addWeek();
        }

        return $weeklyData;
    }

    public function getTopCategories(?string $month = null, ?string $walletId = null, int $limit = 5): ServiceResult
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return ServiceResult::error(__('messages.unauthenticated'), Response::HTTP_UNAUTHORIZED);
            }

            $targetMonth = $month ? Carbon::parse($month . '-01') : Carbon::now();
            
            if ($walletId) {
                $wallet = $this->walletRepository->findByUuid($walletId);
                if (!$wallet || $wallet->user_id !== $userId) {
                    return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
                }
            }

            $startDate = $targetMonth->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $targetMonth->copy()->endOfMonth()->format('Y-m-d');

            $topCategories = $this->transactionRepository->getTopCategories(
                $userId,
                $startDate,
                $endDate,
                $walletId,
                $limit
            );

            $data = [
                'month' => $targetMonth->format('Y-m'),
                'categories' => $topCategories,
            ];

            return ServiceResult::success($data);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }

    public function getNetInMonth(?string $month = null, ?string $walletId = null): ServiceResult
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return ServiceResult::error(__('messages.unauthenticated'), Response::HTTP_UNAUTHORIZED);
            }

            $targetMonth = $month ? Carbon::parse($month . '-01') : Carbon::now();
            
            if ($walletId) {
                $wallet = $this->walletRepository->findByUuid($walletId);
                if (!$wallet || $wallet->user_id !== $userId) {
                    return ServiceResult::error(__('messages.wallet_transaction.wallet_not_found'), Response::HTTP_NOT_FOUND);
                }
            }

            $startDate = $targetMonth->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $targetMonth->copy()->endOfMonth()->format('Y-m-d');

            $incomes = $this->transactionRepository->getIncomesByDateRange(
                $userId,
                $startDate,
                $endDate,
                $walletId
            );

            $expenses = $this->transactionRepository->getExpensesByDateRange(
                $userId,
                $startDate,
                $endDate,
                $walletId
            );

            $data = [
                'month' => $targetMonth->format('Y-m'),
                'total_income' => $incomes['total'],
                'total_expense' => $expenses['total'],
                'net' => $incomes['total'] - $expenses['total'],
                'income_count' => $incomes['count'],
                'expense_count' => $expenses['count'],
            ];

            return ServiceResult::success($data);
        } catch (\Exception $e) {
            return ServiceResult::error(__('messages.error'));
        }
    }
}
