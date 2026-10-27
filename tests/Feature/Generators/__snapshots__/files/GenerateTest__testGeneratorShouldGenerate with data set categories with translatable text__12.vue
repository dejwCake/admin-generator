<template>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            {{ translations.show_title }}
        </div>
        <div class="card-body">
            <ShowRow :label="translations.columns.id" :value="data.id"/>
            <ShowRow :label="translations.columns.uuid" :value="data.uuid"/>
            <ShowRow :label="translations.columns.user_id" :value="data.user?.name"/>
            <ShowRow :label="translations.columns.title" :value="data.title"/>
            <ShowRow :label="translations.columns.name" :value="data.name"/>
            <ShowRow :label="translations.columns.first_name" :value="data.first_name"/>
            <ShowRow :label="translations.columns.last_name" :value="data.last_name"/>
            <ShowRow :label="translations.columns.subject" :value="data.subject"/>
            <ShowRow :label="translations.columns.email" :value="data.email"/>
            <ShowRow :label="translations.columns.language" :value="data.language"/>
            <ShowRow :label="translations.columns.slug" :value="data.slug"/>
            <ShowRow :label="translations.columns.perex">
                <div v-html="data.perex"></div>
            </ShowRow>
            <ShowRow :label="translations.columns.long_text" :value="data.long_text"/>
            <ShowRow :label="translations.columns.published_at" :value="formatDate(data.published_at)"/>
            <ShowRow :label="translations.columns.published_to" :value="formatDate(data.published_to)"/>
            <ShowRow :label="translations.columns.date_start" :value="formatDate(data.date_start)"/>
            <ShowRow :label="translations.columns.time_start" :value="formatTime(data.time_start)"/>
            <ShowRow :label="translations.columns.date_time_end" :value="formatDatetime(data.date_time_end)"/>
            <ShowRow :label="translations.columns.released_at" :value="formatDatetime(data.released_at)"/>
            <ShowRow :label="translations.columns.text">
                <div v-html="data.text"></div>
            </ShowRow>
            <ShowRow :label="translations.columns.description" :value="Object.values(data.description ?? {}).join(', ')"/>
            <ShowRow :label="translations.columns.enabled">
                <ReadOnlySwitch :modelValue="data.enabled"/>
            </ShowRow>
            <ShowRow :label="translations.columns.send">
                <ReadOnlySwitch :modelValue="data.send"/>
            </ShowRow>
            <ShowRow :label="translations.columns.price" :value="data.price"/>
            <ShowRow :label="translations.columns.rating" :value="data.rating"/>
            <ShowRow :label="translations.columns.views" :value="data.views"/>
            <ShowRow :label="translations.columns.created_by_admin_user_id">
                <UserDetailTooltip
                    v-if="data.created_by_admin_user"
                    :user="data.created_by_admin_user"
                    :datetime-text="translations.created_on + ' ' + formatDatetime(data.created_at)"
                />
            </ShowRow>
            <ShowRow :label="translations.columns.updated_by_admin_user_id">
                <UserDetailTooltip
                    v-if="data.updated_by_admin_user"
                    :user="data.updated_by_admin_user"
                    :datetime-text="translations.updated_on + ' ' + formatDatetime(data.updated_at)"
                />
            </ShowRow>
            <ShowRow :label="translations.columns.created_at" :value="formatDatetime(data.created_at)"/>
            <ShowRow :label="translations.columns.updated_at" :value="formatDatetime(data.updated_at)"/>
            <ShowRow
                :label="translations.relations.posts"
                :value="(data.posts ?? []).map((item) => item.title).join(', ')"
            />
        </div>
        <div class="card-footer">
            <BackButton :url="backUrl" :translations="translations"/>
        </div>
    </div>
</template>

<script setup>
import BackButton from '@craftable/components/BackButton.vue';
import ReadOnlySwitch from '@craftable/components/ReadOnlySwitch.vue';
import ShowRow from '@craftable/components/show/ShowRow.vue';
import UserDetailTooltip from '@craftable/components/UserDetailTooltip.vue';
import {formatDate, formatDatetime, formatTime} from '@craftable/utils/dateFormatters.js';

defineProps({
    data: {type: Object, default: () => ({})},
    translations: {type: Object, default: () => ({})},
    backUrl: {type: String, required: true},
});
</script>
