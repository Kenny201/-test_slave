@extends('layouts.app')

@section('title', 'Загрузка Excel')

@section('content')
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Загрузка Excel-файла</h2>

        @if(session('success'))
            <div class="mb-4 p-4 text-sm text-green-800 bg-green-100 rounded-lg">
                {{ session('success') }}
                @if(session('filePath'))
                    <p>Файл сохранён по пути: <code class="text-gray-700">{{ session('filePath') }}</code></p>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 text-sm text-red-800 bg-red-100 rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('upload.handle') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Выберите Excel-файл</label>
                <input type="file" name="file" id="file"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                @error('file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    id="uploadButton"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Загрузить
            </button>
        </form>

        <div class="mt-8">
            <h2 class="text-xl mb-2">Import Progress</h2>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progressBar" class="bg-blue-500 h-4 rounded-full" style="width: 0%;"></div>
            </div>
            <div id="progressText" class="mt-2 text-gray-700">0% Completed</div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function updateProgress() {
				axios.get('/upload/progress')
				.then(response => {
					const progress = response.data.progress;
					isImporting = response.data.isImporting;
					console.log("Progress received:", progress);

					const progressBar = document.getElementById('progressBar');
					progressBar.style.width = progress + '%';

					const progressText = document.getElementById('progressText');
					progressText.textContent = progress + '% Completed';

					const uploadButton = document.getElementById('uploadButton');

					if (isImporting) {
						uploadButton.disabled = true;
						uploadButton.textContent = 'Идёт импорт...';
						uploadButton.classList.add('opacity-50', 'cursor-not-allowed');
					} else {
						uploadButton.disabled = false;
						uploadButton.textContent = 'Загрузить';
						uploadButton.classList.remove('opacity-50', 'cursor-not-allowed');
					}
				})
				.catch(error => {
					console.error('Error fetching progress:', error);
				});
			}

			setInterval(updateProgress, 2000);
    </script>
@endsection
