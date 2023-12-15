<script setup>
//Importē funkcijas
import { computed } from 'vue';
//Definē mainīgo
const props = defineProps({
  message: Object,
  userId: Number
});
//Funkcijas, kas parāda vai lietotājs, kas izveidojis vēstuli, ir lietoājs,
//kas skatās vēstuli
const isCurrentUser = computed(() => props.message.user_id == props.userId);
</script>
<template>
    <div :class="{ 'flex justify-end': isCurrentUser, 'justify-start': !isCurrentUser }" class="mb-2">
        <div :class="{ 'bg-green-400': isCurrentUser, 'bg-blue-400': !isCurrentUser }" class="p-2 rounded-lg text-white max-w-[70%]">
        {{ message.text }}
        </div>
    </div>
</template>