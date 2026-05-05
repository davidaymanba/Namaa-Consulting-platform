<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Claim;
use App\Models\JournalEntry;
use App\Models\Policy;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $role = $user->role;

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $policyBase = $this->policyScopeForUser($user);
        $claimBase = $this->claimScopeForUser($user);
        $transactionBase = $this->transactionScopeForUser($user);

        $totalActivePolicies = (clone $policyBase)->where('status', 'active')->count();
        $monthlyPremium = (float) (clone $policyBase)->whereBetween('issued_at', [$monthStart, $monthEnd])->sum('net_premium');
        $openClaimsCount = (clone $claimBase)->whereNotIn('status', ['rejected', 'paid'])->count();
        $claimsThisMonth = (float) (clone $claimBase)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('approved_amount');
        $lossRatio = $monthlyPremium > 0 ? round(($claimsThisMonth / $monthlyPremium) * 100, 2) : 0;
        $renewalAlerts = (clone $policyBase)
            ->whereIn('status', ['active', 'renewed'])
            ->whereBetween('end_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
            ->count();

        $staleClaims = (clone $claimBase)->whereNotIn('status', ['paid', 'rejected'])->where('last_status_update_at', '<', now()->subDays(30))->count();
        $pendingApprovals = ApprovalRequest::query()->where('status', 'pending')->count();

        $cards = [];
        $alerts = [];
        $quickActions = [];
        $pageTitle = 'لوحة التحكم';
        $pageSubtitle = 'ملخص تشغيلي فوري';

        switch ($role) {
            case 'SUPER_ADMIN':
                $pageTitle = 'لوحة المدير العام';
                $pageSubtitle = 'رؤية شاملة لجميع فروع النظام';
                $cards = [
                    ['label' => 'الوثائق النشطة', 'value' => number_format($totalActivePolicies), 'hint' => 'على مستوى الشركة'],
                    ['label' => 'أقساط الشهر', 'value' => number_format($monthlyPremium, 2).' SAR', 'hint' => 'إجمالي إنتاج الشهر'],
                    ['label' => 'المطالبات المفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'تحتاج متابعة'],
                    ['label' => 'نسبة الخسارة', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'مطالبات/أقساط'],
                    ['label' => 'موافقات معلقة', 'value' => number_format($pendingApprovals), 'hint' => 'طلبات بانتظار القرار'],
                    ['label' => 'تنبيهات تجديد', 'value' => number_format($renewalAlerts), 'hint' => 'خلال 30 يوم'],
                ];
                $quickActions = [
                    ['label' => 'إصدار وثيقة', 'route' => route('policies.create')],
                    ['label' => 'تسجيل مطالبة', 'route' => route('claims.create')],
                    ['label' => 'التقارير', 'route' => route('reports.index')],
                ];
                break;

            case 'BRANCH_MANAGER':
                $pageTitle = 'لوحة مدير الفرع';
                $pageSubtitle = 'متابعة أداء الفرع وإجراءات الاعتماد';
                $cards = [
                    ['label' => 'وثائق الفرع النشطة', 'value' => number_format($totalActivePolicies), 'hint' => 'نشطة الآن'],
                    ['label' => 'أقساط الفرع الشهرية', 'value' => number_format($monthlyPremium, 2).' SAR', 'hint' => 'إنتاج هذا الشهر'],
                    ['label' => 'مطالبات الفرع المفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'حالات قيد المعالجة'],
                    ['label' => 'نسبة خسارة الفرع', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'مؤشر اكتتاب الفرع'],
                    ['label' => 'موافقات معلقة', 'value' => number_format($pendingApprovals), 'hint' => 'خصومات/إلغاءات/مطالبات'],
                    ['label' => 'تجديدات قريبة', 'value' => number_format($renewalAlerts), 'hint' => 'خلال 30 يوم'],
                ];
                $quickActions = [
                    ['label' => 'إصدار وثيقة', 'route' => route('policies.create')],
                    ['label' => 'تسجيل مطالبة', 'route' => route('claims.create')],
                    ['label' => 'إعادة التأمين', 'route' => route('reinsurance.index')],
                ];
                break;

            case 'UNDERWRITER':
                $issuedByMe = (clone $policyBase)->where('issued_by', $user->id)->whereBetween('issued_at', [$monthStart, $monthEnd])->count();
                $draftCount = (clone $policyBase)->where('status', 'draft')->count();
                $avgPremium = (float) (clone $policyBase)->whereBetween('issued_at', [$monthStart, $monthEnd])->avg('net_premium');
                $discountRequests = ApprovalRequest::query()->where('request_type', 'premium_discount')->where('status', 'pending')->count();

                $pageTitle = 'لوحة الاكتتاب';
                $pageSubtitle = 'إصدار الوثائق والتسعير والمخاطر';
                $cards = [
                    ['label' => 'وثائق أصدرتها هذا الشهر', 'value' => number_format($issuedByMe), 'hint' => 'حسب مستخدم الاكتتاب'],
                    ['label' => 'مسودات قيد الإكمال', 'value' => number_format($draftCount), 'hint' => 'تحتاج اعتماد/إصدار'],
                    ['label' => 'متوسط القسط', 'value' => number_format($avgPremium, 2).' SAR', 'hint' => 'متوسط إصدار الشهر'],
                    ['label' => 'تجديدات قريبة', 'value' => number_format($renewalAlerts), 'hint' => 'فرصة تجديد مبكر'],
                    ['label' => 'طلبات خصم > 15%', 'value' => number_format($discountRequests), 'hint' => 'بانتظار موافقة'],
                    ['label' => 'نسبة خسارة المحفظة', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'مؤشر جودة الاكتتاب'],
                ];
                $quickActions = [
                    ['label' => 'إصدار وثيقة جديدة', 'route' => route('policies.create')],
                    ['label' => 'إدارة الوثائق', 'route' => route('policies.index')],
                    ['label' => 'التقارير', 'route' => route('reports.index')],
                ];
                break;

            case 'CLAIMS_OFFICER':
                $largeOpenClaims = (clone $claimBase)->where('is_large_claim', true)->whereNotIn('status', ['paid', 'rejected'])->count();
                $paidThisMonth = (float) (clone $claimBase)->where('status', 'paid')->whereBetween('updated_at', [$monthStart, $monthEnd])->sum('paid_amount');
                $investigationCount = (clone $claimBase)->whereIn('status', ['under_investigation', 'surveyor_assigned', 'report_received'])->count();

                $pageTitle = 'لوحة المطالبات';
                $pageSubtitle = 'متابعة المطالبات والتسويات';
                $cards = [
                    ['label' => 'مطالبات مفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'كل الحالات غير المغلقة'],
                    ['label' => 'مطالبات كبيرة', 'value' => number_format($largeOpenClaims), 'hint' => 'أكبر من 10,000 SAR'],
                    ['label' => 'مطالبات متأخرة', 'value' => number_format($staleClaims), 'hint' => 'بدون تحديث > 30 يوم'],
                    ['label' => 'تحت التحقيق', 'value' => number_format($investigationCount), 'hint' => 'تحتاج متابعة فنية'],
                    ['label' => 'مدفوعات الشهر', 'value' => number_format($paidThisMonth, 2).' SAR', 'hint' => 'إجمالي المصروف'],
                    ['label' => 'نسبة الخسارة', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'مؤشر المطالبات'],
                ];
                $quickActions = [
                    ['label' => 'تسجيل مطالبة', 'route' => route('claims.create')],
                    ['label' => 'قائمة المطالبات', 'route' => route('claims.index')],
                    ['label' => 'التقارير', 'route' => route('reports.index')],
                ];
                if ($staleClaims > 0) {
                    $alerts[] = 'يوجد مطالبات متأخرة بدون تحديث لأكثر من 30 يوم.';
                }
                break;

            case 'ACCOUNTANT':
                $entriesMonth = JournalEntry::query()->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->count();
                $premiumCollections = (float) (clone $transactionBase)->where('type', 'premium_collection')->where('status', 'completed')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');
                $claimsPayments = (float) (clone $transactionBase)->where('type', 'claim_payment')->where('status', 'completed')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');
                $receivableDebit = (float) JournalEntry::query()->where('debit_account', '1100-Accounts Receivable')->sum('amount');
                $receivableCredit = (float) JournalEntry::query()->where('credit_account', '1100-Accounts Receivable')->sum('amount');
                $receivablesBalance = $receivableDebit - $receivableCredit;
                $cashIn = (float) JournalEntry::query()->where('debit_account', '1000-Cash/Bank')->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
                $cashOut = (float) JournalEntry::query()->where('credit_account', '1000-Cash/Bank')->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');

                $pageTitle = 'لوحة النظام المالي';
                $pageSubtitle = 'الحركة اليومية والقيود المحاسبية';
                $cards = [
                    ['label' => 'قيود الشهر', 'value' => number_format($entriesMonth), 'hint' => 'قيود محاسبية مسجلة'],
                    ['label' => 'تحصيل أقساط', 'value' => number_format($premiumCollections, 2).' SAR', 'hint' => 'المحصل هذا الشهر'],
                    ['label' => 'مدفوعات مطالبات', 'value' => number_format($claimsPayments, 2).' SAR', 'hint' => 'المسدد هذا الشهر'],
                    ['label' => 'رصيد الذمم', 'value' => number_format($receivablesBalance, 2).' SAR', 'hint' => 'مدين - دائن'],
                    ['label' => 'صافي التدفق النقدي', 'value' => number_format($cashIn - $cashOut, 2).' SAR', 'hint' => 'داخل - خارج'],
                    ['label' => 'نسبة الخسارة', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'لمتابعة الربحية'],
                ];
                $quickActions = [
                    ['label' => 'النظام المالي', 'route' => route('finance.index')],
                    ['label' => 'إعادة التأمين', 'route' => route('reinsurance.index')],
                    ['label' => 'التقارير', 'route' => route('reports.index')],
                ];
                break;

            case 'BROKER':
                $brokerId = (int) data_get($user->permissions, 'broker_id', 0);
                $commissionRate = 0;
                if ($brokerId > 0) {
                    $commissionRate = (float) DB::table('brokers')->where('id', $brokerId)->value(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(commission_rates, '$.car'))"));
                }
                $estimatedCommission = round($monthlyPremium * ($commissionRate / 100), 2);

                $pageTitle = 'لوحة الوسيط';
                $pageSubtitle = 'الإنتاج والعمولات وخسائر المحفظة';
                $cards = [
                    ['label' => 'وثائقي النشطة', 'value' => number_format($totalActivePolicies), 'hint' => 'الوثائق التابعة لك'],
                    ['label' => 'إنتاج هذا الشهر', 'value' => number_format($monthlyPremium, 2).' SAR', 'hint' => 'صافي أقساط الوثائق'],
                    ['label' => 'مطالبات مفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'مطالبات عملائك'],
                    ['label' => 'نسبة الخسارة', 'value' => number_format($lossRatio, 2).'%', 'hint' => 'للمحفظة الخاصة بك'],
                    ['label' => 'عمولة تقديرية', 'value' => number_format($estimatedCommission, 2).' SAR', 'hint' => 'حسب نسبة عمولة السيارة'],
                    ['label' => 'تجديدات قريبة', 'value' => number_format($renewalAlerts), 'hint' => 'فرص متابعة العملاء'],
                ];
                $quickActions = [
                    ['label' => 'الوثائق', 'route' => route('policies.index')],
                    ['label' => 'المطالبات', 'route' => route('claims.index')],
                    ['label' => 'العملاء', 'route' => route('crm.index')],
                ];
                break;

            case 'CLIENT':
                $myPaidClaims = (float) (clone $claimBase)->where('status', 'paid')->sum('paid_amount');
                $nextRenewal = (clone $policyBase)->whereDate('end_date', '>=', now())->orderBy('end_date')->value('end_date');
                $totalPremiumAll = (float) (clone $policyBase)->sum('net_premium');

                $pageTitle = 'لوحة العميل';
                $pageSubtitle = 'وثائقك ومطالباتك ومدفوعاتك';
                $cards = [
                    ['label' => 'وثائقك النشطة', 'value' => number_format($totalActivePolicies), 'hint' => 'سارية الآن'],
                    ['label' => 'إجمالي أقساطك', 'value' => number_format($totalPremiumAll, 2).' SAR', 'hint' => 'كل الوثائق'],
                    ['label' => 'مطالبات مفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'بانتظار الإغلاق'],
                    ['label' => 'مطالبات مدفوعة', 'value' => number_format($myPaidClaims, 2).' SAR', 'hint' => 'إجمالي ما تم صرفه'],
                    ['label' => 'موعد التجديد القادم', 'value' => $nextRenewal ? (string) $nextRenewal : '-', 'hint' => 'أقرب وثيقة للانتهاء'],
                    ['label' => 'تنبيهات التجديد', 'value' => number_format($renewalAlerts), 'hint' => 'خلال 30 يوم'],
                ];
                $quickActions = [
                    ['label' => 'وثائقي', 'route' => route('policies.index')],
                    ['label' => 'مطالباتي', 'route' => route('claims.index')],
                    ['label' => 'الملف الشخصي', 'route' => route('profile.edit')],
                ];
                break;

            default:
                $cards = [
                    ['label' => 'الوثائق النشطة', 'value' => number_format($totalActivePolicies), 'hint' => 'ملخص عام'],
                    ['label' => 'المطالبات المفتوحة', 'value' => number_format($openClaimsCount), 'hint' => 'ملخص عام'],
                ];
                $quickActions = [['label' => 'الملف الشخصي', 'route' => route('profile.edit')]];
                break;
        }

        if ($renewalAlerts > 0) {
            $alerts[] = 'يوجد '.$renewalAlerts.' وثيقة قريبة من الانتهاء خلال 30 يوم.';
        }

        $premiumByType = (clone $policyBase)
            ->select('type', DB::raw('SUM(net_premium) as total'))
            ->whereBetween('issued_at', [$monthStart, $monthEnd])
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        $policyDistribution = (clone $policyBase)
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        $trend = collect(range(11, 0, -1))->map(function ($offset) use ($now, $user) {
            $month = $now->copy()->subMonths($offset);
            $policies = $this->policyScopeForUser($user)
                ->whereYear('issued_at', $month->year)
                ->whereMonth('issued_at', $month->month)
                ->sum('net_premium');

            $claims = $this->claimScopeForUser($user)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('approved_amount');

            return [
                'month' => $month->format('M Y'),
                'premiums' => round((float) $policies, 2),
                'claims' => round((float) $claims, 2),
            ];
        })->values();

        $latestTransactions = (clone $transactionBase)
            ->with(['policy', 'claim'])
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'cards' => $cards,
            'alerts' => array_unique($alerts),
            'quickActions' => $quickActions,
            'premiumByType' => $premiumByType,
            'policyDistribution' => $policyDistribution,
            'trend' => $trend,
            'latestTransactions' => $latestTransactions,
        ]);
    }

    private function policyScopeForUser(User $user): Builder
    {
        $query = Policy::query();

        if ($user->role === 'BRANCH_MANAGER') {
            $query->where('branch_id', $user->branch_id);
        }

        if ($user->role === 'BROKER') {
            $brokerId = data_get($user->permissions, 'broker_id');
            $query->where('broker_id', $brokerId ?: -1);
        }

        if ($user->role === 'CLIENT') {
            $clientId = data_get($user->permissions, 'client_id');
            $query->where('client_id', $clientId ?: -1);
        }

        return $query;
    }

    private function claimScopeForUser(User $user): Builder
    {
        return Claim::query()->whereHas('policy', function ($q) use ($user) {
            if ($user->role === 'BRANCH_MANAGER') {
                $q->where('branch_id', $user->branch_id);
            }

            if ($user->role === 'BROKER') {
                $q->where('broker_id', data_get($user->permissions, 'broker_id', -1));
            }

            if ($user->role === 'CLIENT') {
                $q->where('client_id', data_get($user->permissions, 'client_id', -1));
            }
        });
    }

    private function transactionScopeForUser(User $user): Builder
    {
        $query = Transaction::query();

        if (in_array($user->role, ['BRANCH_MANAGER', 'BROKER', 'CLIENT'], true)) {
            $query->whereHas('policy', function ($q) use ($user) {
                if ($user->role === 'BRANCH_MANAGER') {
                    $q->where('branch_id', $user->branch_id);
                }

                if ($user->role === 'BROKER') {
                    $q->where('broker_id', data_get($user->permissions, 'broker_id', -1));
                }

                if ($user->role === 'CLIENT') {
                    $q->where('client_id', data_get($user->permissions, 'client_id', -1));
                }
            });
        }

        return $query;
    }
}
