<?php

namespace Opcodes\LogViewer\Http\Livewire;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Opcodes\LogViewer\Facades\LogViewer;

class FileList extends Component
{
    public ?string $selectedFileIdentifier = null;

    public bool $shouldLoadFiles = false;

    public function mount(string $selectedFileIdentifier = null)
    {
        $this->selectedFileIdentifier = $selectedFileIdentifier;

        if (! LogViewer::getFile($this->selectedFileIdentifier)) {
            $this->selectedFileIdentifier = null;
        }
    }

    public function render()
    {
        if ($this->shouldLoadFiles) {
            $files = LogViewer::getFiles();

            foreach ($files as $file) {
                $file->logs()->scan();
            }

            $files = $files->sortByDesc->latestTimestamp();
        }

        return view('log-viewer::livewire.file-list', [
            'files' => $files ?? [],
        ]);
    }

    public function loadFiles()
    {
        $this->shouldLoadFiles = true;
    }

    public function selectFile(string $name)
    {
        $this->selectedFileIdentifier = $name;
    }

    public function deleteFile(string $fileIdentifier)
    {
        $file = LogViewer::getFile($fileIdentifier);

        if ($file) {
            Gate::authorize('deleteLogFile', $file);
            $file->delete();
        }

        if ($this->selectedFileIdentifier === $fileIdentifier) {
            $this->selectedFileIdentifier = null;
            $this->emit('fileSelected', $this->selectedFileIdentifier);
        }
    }

    public function clearCache(string $fileIdentifier)
    {
        LogViewer::getFile($fileIdentifier)?->clearCache();

        if ($this->selectedFileIdentifier === $fileIdentifier) {
            $this->emit('fileSelected', $this->selectedFileIdentifier);
        }
    }
}
