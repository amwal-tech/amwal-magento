import React from 'react'
import { AmwalInstallmentsTimeline } from 'amwal-checkout-button-react'

interface AmwalMagentoInstallmentsTimelineProps {
  enableInstallments: boolean
  locale?: string
  amount?: number
  installmentsCount?: number
  border: boolean
}

const AmwalMagentoInstallmentsTimeline: React.FC<AmwalMagentoInstallmentsTimelineProps> = ({
  enableInstallments,
  locale,
  amount,
  installmentsCount,
  border
}: AmwalMagentoInstallmentsTimelineProps): JSX.Element => (
    <AmwalInstallmentsTimeline
        enableInstallments={enableInstallments}
        locale={locale}
        amount={amount}
        installmentsCount={installmentsCount}
        border={border}
    />
)

export default AmwalMagentoInstallmentsTimeline
