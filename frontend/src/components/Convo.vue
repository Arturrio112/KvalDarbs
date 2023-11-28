<script setup>
import { onMounted, ref, computed, onUpdated } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Message from '../components/Message.vue';

const props = defineProps({ convo: Object, userId: Number });
const userId = props.userId;
const convo = ref(props.convo);
const token = localStorage.getItem('authToken')
const newMessage = ref('');
const scrollContainer = ref(null);
const sendMessage = async() => {
    if (newMessage.value.trim() !== '') {
        let formData = new FormData();
        formData.append('user_id', userId);
        formData.append('conversation_id', convo.value.conversation_id);
        formData.append('text', newMessage.value);

        try {
            let res = await axios.post(`http://localhost:8000/api/convo/${convo.value.conversation_id}/message/new`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            console.log(res.data); 
            convo.value.messages.unshift(res.data.data.msg);
            newMessage.value = '';
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
};
const scrollToBottom = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
  }
};
onMounted(() => {
  scrollToBottom();
});
onUpdated(() => {
  scrollToBottom();
});

</script>

<template>
    <div class="flex flex-col space-y-2 h-full">
        <div class="h-[85vh] overflow-y-scroll no-scrollbar" ref="scrollContainer">
            <div v-for="message in convo.messages.slice().reverse()" :key="message.id">
                <Message :message="message" :userId="userId" />
            </div>
        </div>
        <div class="h-[15vh] w-full ">
            <div class="mt-4 w-full flex items-center">
                <input v-model="newMessage" type="text" placeholder="Type your message..." class="flex-1 p-2 border border-gray-300 rounded">
                <button @click="sendMessage" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Send
                </button>
            </div>
        </div>
    </div>
</template>

