<script lang="ts" setup>
  const props = defineProps<{ item: any, column: string }>()

  function getColor () {
    if (props.item[props.column]?.color) {
      return props.item[props.column].color
    }

    switch (props.item[props.column].id) {
      case 'in':
      case 'sale':
      case 'paid':
      case 'order':
      case 'volume':
      case 'original':
      case 'full_time':
      case 'created':
      case 'usage':
      case 'unlocked':
      case 'overtime_request_approved':
      case 'biometric':
      case 'cash':
      case 'check_in':
      case 'check_out':
      case 'present': {
        return 'success'
      }
      case 'out':
      case 'unpaid':
      case 'billing':
      case 'expire':
      case 'none':
      case 'credit_note':
      case 'return':
      case 'rejected':
      case 'overtime_request_rejected':
      case 'cancelled':
      case 'overtime_request_cancelled':
      case 'carryover':
      case 'qr':
      case 'rebuild':
      case 'locked':
      case 'void':
      case 'refund':
      case 'closed':
      case 'absent':
      case 'unapproved': {
        return 'error'
      }
      case 'cancellation':
      case 'count':
      case 'partially_paid':
      case 'vip':
      case 'adjustment':
      case 'manual_adjust':
      case 'api':
      case 'updated':
      case 'adjusted':
      case 'break_start':
      case 'break_end':
      case 'nearest':
      case 'auto_closed':
      case 'deduction':
      case 'percentage':
      case 'closing_adjust': {
        return 'warning'
      }
      case 'pay_in':
      case 'zatca':
      case 'capacity':
      case 'custom':
      case 'circle':
      case 'percent':
      case 'spooler':
      case 'part_time':
      case 'hour':
      case 'submitted':
      case 'accrual':
      case 'manual':
      case 'leave':
      case 'fixed': {
        return 'info'
      }
      case 'global': {
        return 'primary'
      }
      case 'corporate': {
        return 'secondary'
      }
      case 'recalculated':
      case 'calculated':
      case 'recalculate': {
        return '#e84393'
      }
      case 'adjust': {
        return 'info'
      }
      case 'pay_out': {
        return '#fd7e14'
      }
      case 'cash_drop': {
        return '#6f42c1'
      }
      case 'tip_in': {
        return '#20c997'
      }
      case 'tip_out': {
        return '#ffb300'
      }
      case 'correction': {
        return '#6c757d'
      }
      case 'takeaway': {
        return '#F7B731'
      }
      case 'dine_in': {
        return '#3867D6'
      }
      case 'pick_up': {
        return '#20BF6B'
      }
      case 'primary':
      case 'approved':
      case 'regular':
      case 'web_browser_popup':
      case 'square':
      case 'simplified':
      case 'tcp':
      case 'monthly':
      case 'day':
      case 'mobile':
      case 'user':
      case 'mass':
      case 'holiday':
      case 'earning': {
        return 'primary'
      }
      case 'floor':
      case 'system':
      case 'off':
      case 'reviewed':
      case 'both':
      case 'auto_close':
      case 'rectangle':
      case 'usb_raw':
      case 'yearly':
      case 'reversal':
      case 'card':
      case 'standard':
      case 'employer':
      case 'branch': {
        return 'secondary'
      }
      case 'ceiling': {
        return 'info'
      }
      case 'taxable_earning': {
        return 'success'
      }
      case 'taxable_earnings': {
        return 'success'
      }
      case 'non_taxable_earning': {
        return 'info'
      }
      case 'basic_salary': {
        return 'primary'
      }
      case 'gross_earnings': {
        return 'info'
      }
      case 'pre_tax_deduction': {
        return 'warning'
      }
      case 'post_tax_deduction': {
        return 'error'
      }
      case 'not_applicable': {
        return 'secondary'
      }
      case 'posted': {
        return 'success'
      }
      case 'cashier': {
        return '#2D9CDB'
      }
      case 'kitchen': {
        return '#F2994A'
      }
      case 'earn': {
        return '#2ECC71'
      }
      case 'redeem': {
        return '#E74C3C'
      }
      case 'bonus': {
        return '#3498DB'
      }

      default: {
        return 'default'
      }
    }
  }

</script>

<template>
  <VChip
    :color="getColor()"
    size="small"
  >
    <span v-if="item[props.column].icon" class="d-flex justify-center align-center">
      <VIcon :icon="item[props.column].icon" /> &nbsp;
    </span>
    {{ item[props.column].name }}
  </VChip>
</template>
