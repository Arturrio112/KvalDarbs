<script setup>
//Importē funkcijas, ikonas un komponentes
  import {ref, computed, onMounted, watchEffect} from 'vue'
  import { useRouter } from 'vue-router';
  import axios from 'axios';
  import Spider from 'vue-material-design-icons/Spider.vue';
  import Feather from 'vue-material-design-icons/Feather.vue';
  import Close from 'vue-material-design-icons/Close.vue';
  import ChevronDown from 'vue-material-design-icons/ChevronDown.vue';
  import Earth from 'vue-material-design-icons/Earth.vue';
  import ImageOutline from 'vue-material-design-icons/ImageOutline.vue';
  import Emoticon from 'vue-material-design-icons/Emoticon.vue';
  import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue';
  import MenuItem from '../components/MenuItem.vue'
  import Post from '../components/Post.vue'
  import SearchBar from '../components/SearchBar.vue'
  import data from "emoji-mart-vue-fast/data/all.json"
  import "emoji-mart-vue-fast/css/emoji-mart.css"
  import { Picker, EmojiIndex } from "emoji-mart-vue-fast/src"
  //Definē mainīgos
  let textarea = ref(null)
  let createPost = ref(false)
  let postsLoaded = ref(false)
  let post = ref('')
  let followedPosts=ref([])
  let file=ref('')
  let posts=ref([])
  const newPostErrors = ref([])
  let showUpload = ref('')
  let uploadType = ref('')
  const user = ref(null);
  const loading = ref(true)
  const userDataLoaded = ref(false);
  const router = useRouter();
  let activeSection = ref('for_you')
  let showEmojiPicker = ref(false)
  let emojiIndex = new EmojiIndex(data);
  const mentionResults = ref([]);
  const userId = computed(() => (user.value ? user.value.user_id : null));
  //Funkcija, kas nostrādā skatam ielādējoties
  onMounted(()=>{
    const token = localStorage.getItem('authToken')
    //Ja nav autentifikācijas talons, tad tiek novirzīts uz pieslēgšanās skatu
    if(!token){
      router.push({
        name: 'login'
      })
      return
    }
    
    watchEffect(() => {
      //Iegūst lietotāja datus
      axios.get('http://localhost:8000/api/user-data', {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        console.log(res)
        if(res.data.data && res.data.data.profile){
          user.value =res.data.data.profile
          
          console.log(user.value.id)
          userDataLoaded.value = true;
          loading.value = false
          console.log(user.value)
        }else{
          router.push({
            name: 'login'
          })
        }
        //Iegūst lietotāju, kuriem seko, rakstus
        axios.get(`http://localhost:8000/api/follow/${user.value.id}`,{
          params:{
            userId: user.value.id
          },
          headers: {
            Authorization: `Bearer ${token}`
          }
        }).then((res)=>{
          console.log(res)
          followedPosts.value = res.data.data.posts
        }).catch((err)=>{
          console.error('Error fetching data', err)
        })
      }).catch((err)=>{
        console.error('Error fetching data', err)
        router.push({
          name: 'login'
        })
        loading.value = false;
      })
      //Iegūst visus rakstus
      axios.get('http://localhost:8000/api/post', {
        headers: {
          Authorization: `Bearer ${token}`
        }
      }).then((res)=>{
        console.log(res.data.data.posts); 
        let temp = res.data.data.posts
        console.log(temp)
        posts.value = res.data.data.posts;
        console.log(posts.value)
        postsLoaded.value=true
      }).catch((err)=>{
        console.error('Error fetching data', err)
        loading.value = false;
      })
    })
  }) 
  //Funkcija, kas noformē raksta lauku 
  const textareaInput = (e)=>{
    const token = localStorage.getItem('authToken')
    textarea.value.style.height = "auto"
    textarea.value.style.height = `${e.target.scrollHeight}px`
    const words = post.value.split(' ');
    const lastWord = words[words.length - 1];

    if (lastWord.startsWith('@') && lastWord.length > 1&&words.length>0) {
      const query = lastWord.substring(1); 
      axios.get('http://localhost:8000/api/search/mention',{ 
        params: {
           query 
        },
        headers: {
          Authorization: `Bearer ${token}`
        }
      })
      .then((response) => {
        mentionResults.value = response.data.data.users;
      })
      .catch((error) => {
        console.error(error);
      });
    } else {
      mentionResults.value = []; 
    }
  }
  //Funkcija, kas pievieno pieminēto lietotāju tekstā
  const insertMention = (user) => {
    const currentPost = post.value;
    const words = currentPost.split(' ');
    const lastWord = words[words.length - 1];
    const mentionPrefixRemoved = lastWord.startsWith('@') ? lastWord.substring(1) : lastWord;
    const newPost = currentPost.slice(0, -mentionPrefixRemoved.length) + user.name + ' ';
    post.value = newPost;
    mentionResults.value = [];
    textarea.value.focus();
  };
  //Pārbauda vai faila tips ir atļauts
  const isAllowedType = (type)=>{
    const allowedTypes = ['jpeg', "jpg", 'png', 'gif'];

    const fileExtension = type.split('/')[1];

    return allowedTypes.includes(fileExtension);
  }
  //Funkcija, kas iegūst failu
  const getFile = (e)=>{
    const selectedFile = e.target.files[0];
    newPostErrors.value = []
    if (selectedFile) {
        const fileExtension = selectedFile.name.split('.').pop().toLowerCase();
        const fileType = selectedFile.type
        if (isAllowedType(fileType)) {
            file.value = selectedFile;
            showUpload.value = URL.createObjectURL(selectedFile);
            uploadType.value = fileExtension;
        } else {
           
            newPostErrors.value.push('Added file format is not supported, supported file formats - jpeg, png, jpg, gif ')
            
            e.target.value = null;
          
            file.value = null;
            showUpload.value = '';
            uploadType.value = '';
        }
    }
  }
  //Funkcija, kas aizver logu
  const closeMessageBox=()=>{
    createPost.value=false
    post.value=''
    showUpload.value=''
    uploadType.value=''
  }
  //Funkcija, kas atver un aizver emocijzīmju logu
  const toggleEmojiPicker = () =>{
    showEmojiPicker.value = !showEmojiPicker.value;
  }
  //Funkcija, ka pievieno emocijzīmi
  const handleEmojiClick = (emoji) => {
    post.value += emoji.native;
  };
  //Funkcija, kas noformē raksta datus un nosūta pieprisījumu
  const handlePostSubmit = async () => {
    const token = localStorage.getItem('authToken');
    
    if (!token) {
        router.push({
            name: 'login'
        });
        return;
    }
    post.value = post.value.trim()
    if(newPostErrors.length>0){
        return
    }
    newPostErrors.value = []
    if(post.value == ''){
        newPostErrors.value.push('Text cannot be empty')
        return
    }
    const formData = new FormData(); 
    
    formData.append('text', post.value);
    formData.append('user_id', user.value.user_id);
    
    if (file.value) {
        formData.append('media', file.value);
        formData.append('fileFormat', uploadType.value);
    } else {
        formData.append('media', ''); 
        formData.append('fileFormat', '');
    }
    
    axios.post('http://localhost:8000/api/post', formData, {
        headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
        }
    }).then((res) => {
        console.log(res);
        closeMessageBox();
        window.location.reload();
    }).catch((err) => {
        console.error('Error fetching data', err);
        loading.value = false;
    });
};

  

</script>

<template>
  <div>
  <div class="fixed w-full">
    <div class="max-w-[1400px] flex mx-auto">
      <div class="lg:w-3/12 w-[60px] h-[100vh] max-w-[350px] lg:px-4 lg:mx-auto">
        <div class="p-2 px-3 mb-4">
          <Spider fillColor="#fca521" :size="37"/>
        </div>
        <template v-if="userDataLoaded">
        <MenuItem iconString="Home"/>
        <router-link :to="'/explore'">
          <MenuItem iconString="Explore"/>
        </router-link>
        <router-link :to="'/messages'">
          <MenuItem iconString="Messages"/>
        </router-link>
        <router-link :to="`/user/${userId}`">
          <MenuItem iconString="Profile"/>
        </router-link>
        </template>
        <button
          @click="createPost=true"
          class="
            lg:w-full
            mt-8
            ml-2
            text-black
            font-extrabold
            text-[22px]
            bg-[#fca521]
            p-3
            px-3
            rounded-full
            cursor-pointer
         "
        >
          <span class="lg:block hidden">Post</span>
          <span class="block lg:hidden"><Feather/></span>
        </button>
      </div>

      <div class="lg:w-7/12 w-11/12 border-x border-gray-800 relative">
        <div class=" bg-gray-300 bg-opacity-50 backdrop-blur-md z-10 absolute w-full">
          <div class="border-gray-800 border-b w-full">
            <div class="w-full text-black text-[22px] font-extrabold p-4">
              Home
            </div>
            <div class="flex">
              <div
                class="
                  flex
                  items-center
                  justify-center
                  w-full
                  h-[60px]
                  text-black
                  text-[17px]
                  font-extrabold
                  p-4
                  hover:bg-gray-500
                  hover:bg-opacity-30
                  cursor-pointer
                  transition
                  duration-200
                  ease-in-out
                "
              >
                <div
                  @click="activeSection = 'for_you'"
                  :class="{ 'border-b-[#fca521]': activeSection === 'for_you' }"
                  class="
                    inline-block
                    text-center
                    border-b-4
                    border-b-gray
                    h-[60px]
                  "
                >
                  <div class="my-auto mt-4">All</div>
                </div>
              </div>
              <div
                class="
                  flex
                  items-center
                  justify-center
                  w-full
                  h-[60px]
                  text-black
                  text-[17px]
                  font-extrabold
                  p-4
                  hover:bg-gray-500
                  hover:bg-opacity-30
                  cursor-pointer
                  transition
                  duration-200
                  ease-in-out
                "
              >
                <div
                  @click="activeSection = 'following'"
                  :class="{ 'border-b-[#fca521]': activeSection === 'following' }"
                  class="
                    inline-block
                    text-center
                    border-b-4
                    border-b-gray
                    h-[60px]
                  "
                >
                  <div class="my-auto mt-4">Following</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="
          absolute
          top-0
          h-full
          overflow-y-scroll
          no-scrollbar
          w-full      
        "
        >
          <div v-if="postsLoaded && userDataLoaded && activeSection=='for_you'">
            <div class="mt-[126px]"></div>
            <div  class="flex" v-for="post in posts" :key="post.id">

                <Post :post="post" :userId="userId"/>
            </div>
          </div>
          <div v-else>
            <div class="mt-[126px]"></div>
            <div  class="flex" v-for="post in followedPosts" :key="post.id">

                <Post :post="post" :userId="userId"/>
            </div>
          </div>
          <div class="pb-4"></div>
        </div>
      </div>
      <div class="
        lg:block
        hidden
        lg:w-4/12
        h-screen
        border-l
        border-gray-800
        pl-4
      ">
        <SearchBar/>
      </div>
    </div>
  </div>
  <div id="OverlaySection" v-if="createPost==true" class="
      fixed
      top-0
      left-0
      w-full
      h-screen
      bg-black
      md:bg-gray-100
      md:bg-opacity-80
      md:p-3
    ">
      <div class="
        md:max-w-2xl
        md:mx-auto
        md:mt-30
        md:rounded-xl
        bg-black
      ">
        <div class="
          flex
          items-center
          justify-between
          md:inline-block
          p-2
          m-2
          rounded-full
          cursor-pointer
        ">
          <div @click="closeMessageBox" class="
            hover:bg-gray-800
            inline-block
            p-2
            rounded-full
            cursor-pointer
          ">
            <Close fillColor="#FFFFFF" :size="28" class="md:block hidden"/>
            <ArrowLeft fillColor="#FFFFFF" :size="28" class="md:hidden block"/>
          </div>
          <button
            @click="handlePostSubmit"
           :disabled="!post"
           :class="post ? 'bg-[#fca521] text-white' : 'bg-[#124D77] text-gray-400'"
           class="
            md:hidden
            font-extrabold
            text-[10px]
            p-1.5
            px-4
            rounded-full
            cursor-pointer
          ">
            Post
          </button>
        </div>
        <div class="w-full flex">
          <div class="ml-3.5 mr-2">
            <img v-if="userDataLoaded" src="" width="55" class="rounded-full " >
            <img v-else src="../assets/pic.png" class="rounded-full" width="55">
          </div>
          <div class="w-[calc(100%-100px)]">
            <div class="inline-block">
              <div class="
                flex
                items-center
                border
                border-white
                rounded-full
              ">
                <span class="text-[#fca521] p-0.5 pl-3.5 font-extrabold">Everyone</span>
                <ChevronDown class="pr-2" fillColor="#fca521" :size="25"/>
              </div>
            </div>
            <div>
              <textarea 
                :oninput="textareaInput"
                cols="30" 
                rows="4" 
                placeholder="What's happening?"
                v-model="post"
                ref="textarea"
                class="
                  w-full
                  bg-black
                  border-0
                  mt-2
                  focus:ring-0
                  text-white
                  text-[19px]
                  font-extrabold
                  min-h-[120px]
                ">
              
              </textarea>
              <div class="relative mb-3">
                <div class="absolute text-black left-0 mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg z-10" v-if="mentionResults.length > 0">
                  <ul class="py-1">
                    <li v-for="user in mentionResults" :key="user.id" @click="insertMention(user)" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                      {{ user.name }}
                    </li>
                  </ul>
                </div>
              </div>
              <div class="w-full"  v-if="showEmojiPicker">
                <Picker
                  :data="emojiIndex" 
                  set="twitter" 
                  @select="handleEmojiClick" 
                />
              </div>
            </div>
            <div class="w-full">
              <video controls v-if="uploadType === 'mp4'" :src="showUpload" class="rounded-xl overflow-auto"/>
              <img v-else :src="showUpload" class="rounded-xl min-w-full">
            </div>
            <div v-if="newPostErrors.length > 0">
                <div v-for="error in newPostErrors" :key="error" >
                  <div class="text-rose-600">{{ error }}</div>
              </div>
            </div>
            <div class="
              flex
              py-2
              items-center
              text-[#fca521]
              font-extrabold
              
            ">
              <Earth class="pr-2" fillColor="#fca521" :size="20"/>Everyone can reply
            </div>
            <div class="border-b border-b-gray-700"></div>
            <div class="
              flex
              items-center
              justify-between
              py-2
            ">
              <div class="flex items-center">
                <div class="
                  hover:bg-gray-800
                  inline-block
                  p-2
                  rounded-full
                  cursor-pointer
                ">
                  <label for="fileUpload" class="cursor-pointer">
                    <ImageOutline fillColor="#fca521" :size="25"/>
                  </label>
                  <input type="file" id="fileUpload" class="hidden" @change="getFile">
                </div>
                <div @click="toggleEmojiPicker" class="
                  hover:bg-gray-800
                  inline-block
                  p-2
                  rounded-full
                  cursor-pointer
                ">
                    <Emoticon fillColor="#fca521" :size="25"/>
                </div>
              </div>
              <button
                @click="handlePostSubmit"
                :disabled="!post"
                :class="post ? 'bg-[#fca521] text-white' : 'bg-[#124D77] text-gray-400'"
                class="
                  hidden
                  md:block
                  font-extrabold
                  text-[10px]
                  p-1.5
                  px-4
                  rounded-full
                  cursor-pointer
                ">
                  Post
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
