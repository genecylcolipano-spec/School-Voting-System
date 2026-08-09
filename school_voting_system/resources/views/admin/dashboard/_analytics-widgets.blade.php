<div class="grid gap-4 xl:grid-cols-12">
    <div class="xl:col-span-6">
        <x-admin-chart-panel
            title="Participation Growth (Events/Voting)"
            subtitle="Monthly turnout and event participation"
            type="line"
            live-key="participation"
            :labels="$analyticsWidgets['participation']['labels']"
            :values="$analyticsWidgets['participation']['values']"
            :y-max="$analyticsWidgets['participation']['yMax']"
            :y-ticks="$analyticsWidgets['participation']['yTicks']"
            :value-suffix="$analyticsWidgets['participation']['valueSuffix']"
            accent="#34d399"
            empty-message="No participation data available."
            :footer-link="route('admin.analytics.index')"
        />
    </div>
    <div class="xl:col-span-6">
        <x-admin-chart-panel
            title="Donation/Fundraising History"
            subtitle="Monthly donation totals for {{ now()->year }}"
            type="bar"
            live-key="fundraising"
            :labels="$analyticsWidgets['fundraising']['labels']"
            :values="$analyticsWidgets['fundraising']['values']"
            :y-max="$analyticsWidgets['fundraising']['yMax']"
            :y-ticks="$analyticsWidgets['fundraising']['yTicks']"
            :value-prefix="$analyticsWidgets['fundraising']['valuePrefix']"
            accent="#818cf8"
            empty-message="No fundraising records available."
            :footer-link="route('admin.analytics.index')"
        />
    </div>
</div>
