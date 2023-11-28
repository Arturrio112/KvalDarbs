<script setup>
  import axios from 'axios'
  import {ref, computed, onMounted, watchEffect, watch} from 'vue'
  import { useRouter, useRoute } from 'vue-router';
  import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
  import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
  import Add from 'vue-material-design-icons/PlusCircle.vue';
  import Close from 'vue-material-design-icons/Close.vue';
  import Convo from '../components/Convo.vue'
  import TrashCanOutline from 'vue-material-design-icons/TrashCanOutline.vue'
  const user = ref(null)
  const userId = ref(null)
  let newConvoOverlay = ref(false)
  const searchUser = ref('');
  const searchResults = ref([]);
  const selectedUser = ref(null);
  const newMessage = ref('');
  const convos = ref([])
  let newConvoErrors = ref([])
  const selectedConversation = ref(null);
  const token = localStorage.getItem('authToken')
  onMounted(()=>{
    if(!token){
      router.push({
        name: 'login'
      })
    }
    watchEffect(()=>{
      axios.get(`http://localhost:8000/api/user-data`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        user.value=res.data.data.profile
        userId.value=res.data.data.profile.user_id
        console.log(user.value, userId.value)
      }).catch((err)=>{
        console.error('Error fetching data', err);
      })
      axios.get(`http://localhost:8000/api/${userId.value}/convo`, {
        headers: {
          Authorization: `Bearer ${token}`
        },
        params:{
          user_id: userId.value
        }
      }).then((res)=>{
        console.log(res)
        convos.value = res.data.data.conversations
        console.log(convos.value)
      }).catch((err)=>{
        console.error('Error fetching data', err);
      })
    })


  })
  const toggleNewConvoOverlay = () => {
    newConvoOverlay.value = !newConvoOverlay.value;
    searchUser.value = ''
  };
  const performUserSearch = ()=>{
    if(searchUser!==''){
      axios.get('http://localhost:8000/api/search',{
        headers:{
          Authorization: `Bearer ${token}`,
        },
        params:{
          'username':searchUser.value
        }
      }).then((res)=>{
        searchResults.value=res.data.data.users
      }).catch((err)=>{
        console.log(err);
        searchResults.value=[]
      })
    }
  }
  const selectUser = (user) => {
    selectedUser.value = user;
    searchUser.value = ''
  };
  const hasConvoStarted = (id) =>{
    return convos.value.some( c=> c.other_user.id == id)
  }
  const sendMessage = () => {
    newConvoErrors.value = []
    newMessage.value = newMessage.value.trim()
    if (newMessage.value !== '' && selectedUser) {
        let user1_id = userId.value;
        let user2_id = selectedUser.value.id;
        if(user1_id == user2_id){
          newConvoErrors.value.push('Cannot start conversation with yourself')
        }else if(hasConvoStarted(user2_id)){
          newConvoErrors.value.push('Conversation with user has already been started')
        }else{
          let formData = new FormData();
          formData.append('user1_id', user1_id);
          formData.append('user2_id', user2_id);
          formData.append('message', newMessage.value);

          axios.post('http://localhost:8000/api/convo/new', formData, {
              headers: {
                  Authorization: `Bearer ${token}`,
              }
          })
          .then((res) => {
              console.log('Message sent successfully:', res);
              window.location.reload();
          })
          .catch((error) => {
              console.error('Error sending message:', error);
          });
        }
    }else{
      newConvoErrors.value.push('Message cannot be empty')
    }
};

  const deselectUser = ()=>{
    selectedUser.value = null;
    newConvoErrors.value =[]
  }
  const selectConversation = (conversation)=>{
    selectedConversation.value = selectedConversation.value === conversation ? null : conversation;
  }
  const handleDelete= ()=>{
    console.log(selectedConversation.value)
    axios.delete(`http://localhost:8000/api/convo/${selectedConversation.value.conversation_id}`,{
      headers:{
        Authorization: `Bearer ${token}`,
      },
      params:{
        'user_id':userId.value,
        'convo_id':selectedConversation.value.conversation_id
      }
    }).then((res)=>{
      console.log('Convo deleted successfully:', res);
      window.location.reload();
    }).catch((err)=>{
      console.error('Error sending message:', err);
    })
  }
watch(searchUser, () => {
  performUserSearch();
});
</script>

<template>
  <div class="fixed w-full">
    <div class="max-w-[1400px] flex mx-auto">
      
      <div class="lg:w-3/12 w-[300px] h-[100vh] max-w-[350px] lg:me-0 lg:px-4 lg:mx-auto border-x border-gray-800 overflow-auto">
        
        <div class="border-gray-800 border-b w-full">
          <div class="w-full text-black text-[22px] font-extrabold p-4 flex">
            <router-link :to="'/'">
                <ArrowLeft fillColor="#000000" :size="28" class="rounded-full hover:bg-gray-300 transition duration-200 ease-in-out block cursor-pointer mr-2"/>
            </router-link>
            <div class="flex">
              Conversations
             </div>
          </div>
        </div>
        <div class="flex justify-center mt-2 border-b border-gray-800">
          <Add fillColor="#000000" @click="toggleNewConvoOverlay" :size="28" class="mb-2 rounded-full hover:bg-gray-300 transition duration-200 ease-in-out block cursor-pointer mr-2"/>
          <div class="flex">
            Start new conversation
          </div>
        </div>
        <div v-if="convos">
          <div v-for="conversation in convos" :key="conversation.conversation_id">
            <div class="flex items-center p-2 border-b border-gray-200 hover:bg-gray-100 cursor-pointer" @click="selectConversation(conversation)">
              <img :src="conversation.other_user.profile.picture"  class="w-10 h-10 rounded-full mr-4">
              <div class="flex flex-col">
                <div class="text-[14px] text-gray-400">{{ conversation.other_user.profile.nickname }}</div>
                <div class="text-black font-extrabold text-[17px]">{{ conversation.messages[0].text }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="lg:w-7/12 w-11/12 border-x border-gray-800 relative h-[100vh] flex flex-col">
        <div class="p-4 bg-gray-300">
          <div v-if="selectedConversation != null" class="flex justify-between items-center p-2 cursor-pointer">
            <div class="flex items-center">
              <router-link :to="`/user/${selectedConversation.other_user.id}`" class="flex items-center p-2 cursor-pointer">
                <img :src="selectedConversation.other_user.profile.picture" class="w-10 h-10 rounded-full mr-4">
                <div class="text-black font-extrabold text-[17px]">{{ selectedConversation.other_user.profile.nickname }}</div>
              </router-link>
            </div>
            <div class="flex items-center cursor-pointer" @click="handleDelete">
              <TrashCanOutline class="pr-3" fillColor="#DC2626" :size="18"/>
              <span class="text-red-600 font-extrabold">Delete</span>
            </div>
          </div>
          <div v-else>
            <p class="text-gray-600">No conversation selected.</p>
          </div>
        </div>
        <div class="h-full overflow-auto scrollbar-hide p-4 flex-grow">
          <div v-if="selectedConversation" class="h-full">
            <Convo :convo="selectedConversation" :userId="userId" class="h-full"/>
          </div>
          <div v-else class="flex items-center justify-center h-full">
            <p class="text-gray-600">Please select a conversation to view messages.</p>
          </div>
      </div>
    </div>
    <div class="fixed inset-0 flex items-center justify-center z-50" v-if="newConvoOverlay">
      <div class="bg-gray-800 bg-opacity-80 absolute inset-0"></div>
      <div class="bg-white p-4 rounded-lg z-10">
        <h2 class="text-2xl font-bold mb-4">Start New Conversation</h2>
        <div v-if="selectedUser">
          <div class="flex justify-between">
            <div class="flex items-center mb-4">
              <img :src="selectedUser.profile.picture" alt="User Avatar" class="w-10 h-10 rounded-full mr-2">
              <div>
                <div class="text-[14px] text-gray-400">@{{ selectedUser.name }}</div>
                <div class="text-black font-extrabold text-[17px]">
                  <div class="flex items-center">
                    {{ selectedUser.profile.nickname }}
                    <CheckDecagram v-if="selectedUser.profile.verified" fillColor="#fca521" :size="18"/>
                  </div>
                </div>
              </div>
            </div>
            <Close @click="deselectUser" fillColor="#000000" :size="28" class="block cursor-pointer"/>
          </div>
          <input
            v-model="newMessage"
            type="text"
            placeholder="Type your message..."
            class="w-full p-2 border border-gray-300 rounded mb-4"
          />
          <div v-if="newConvoErrors.length > 0">
            <div v-for="error in newConvoErrors" :key="error" >
              <div class="text-rose-600">{{ error }}</div>
            </div>
          </div>
          <button @click="sendMessage" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
            Send
          </button>
        </div>
        <div v-else>
          <input
            v-model="searchUser"
            type="text"
            placeholder="Search for a user..."
            class="w-full p-2 border border-gray-300 rounded mb-4"
          />
          <div v-if="searchResults.length > 0" class="overflow-auto">
            <h3 class="text-lg font-semibold mb-2">Search Results:</h3>
            <div v-for="user in searchResults" :key="user.id">
              <div class="w-full cursor-pointer p-2 border-b border-gray-200 hover:bg-gray-100" @click="selectUser(user)">
                <div class="flex items-center">
                  <img :src="user.profile.picture" alt="User Avatar" class="w-10 h-10 rounded-full mr-4">
                  <div>
                    <div class="text-[14px] text-gray-400">@{{ user.name }}</div>
                    <div class="text-black font-extrabold text-[17px]">
                      <div class="flex items-center">
                        {{ user.profile.nickname }}
                        <CheckDecagram v-if="user.profile.verified" fillColor="#fca521" :size="18" class="ml-1"/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else>
            <p>No users found.</p>
          </div>
        </div>
        <button @click="toggleNewConvoOverlay" class="mt-4 bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700">
          Close
        </button>
      </div>
    </div>
  </div>
  </div>
</template>