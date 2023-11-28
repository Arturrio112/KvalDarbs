<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Magnify from 'vue-material-design-icons/Magnify.vue';
import CheckDecagram from 'vue-material-design-icons/CheckDecagram.vue'
const router = useRouter();
let searchQuery = ref('');
let searchResults = ref([]);
const token = localStorage.getItem('authToken');
const performUserSearch = ()=>{
  if(searchQuery!==''){
    axios.get('http://localhost:8000/api/search',{
      headers:{
        Authorization: `Bearer ${token}`,
      },
      params:{
        'username':searchQuery.value
      }
    }).then((res)=>{
      searchResults.value=res.data.data.users
    }).catch((err)=>{
      console.log(err);
      searchResults.value=[]
    })
  }
}
watch(searchQuery, () => {
  performUserSearch();
});
const handleProfile = (id)=>{
  router.push({name: 'profile', params: {id: id}})
}
</script>
<template>
    <div>
        <div class="
          w-full
          p-1
          mt-2
          px-4
          lg:flex
          items-center
          rounded-full
          hidden
          bg-gray-300
        ">
          <Magnify fillColor="#5e5c5c" :size="25"/>
          <input 
            class="
              appearance-none
              w-full
              border-0
              py-2
              bg-gray-300
              text-gray-800
              placeholder-gray-500
              leading-tight
              focus:ring-0
              outline-none
            "
            type="text"
            placeholder="Search for Users"
            v-model="searchQuery"
            @input="performUserSearch"
          >
        </div>
        <div
          v-if="searchResults.length!==0"
         class="
          w-full
          mt-4
          rounded-lg
          lg:block
          hidden
          bg-gray-300
        ">
          <div class="
            w-full
            p-4
            text-black
            font-extrabold
            mb-6
            text-[20px]
          ">
            Users
          </div>
          <div
          v-for="user in searchResults" :key="user.id"
            class="
              h-[60px]
              hover:bg-gray-500
              rounded-lg
              cursor-pointer
              transition
              duration-200
              ease-in-out
          ">
            <div class="
              flex
              p-3
              justify-between
              h-[80px]
              py-3
            ">
              <div class="w-full" @click="handleProfile(user.id)">
                <div class="text-[14px] text-gray-400">@{{user.name}}</div>
                <div class="w-full text-black font-extrabold mb-6 text-[17px]">
                  <div class="flex">
                    {{user.profile.nickname}}
                    <CheckDecagram v-if="user.profile.verified" fillColor="#fca521" :size="18"/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
</template>