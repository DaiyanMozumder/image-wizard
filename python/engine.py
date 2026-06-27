import sys
import json
from imagewizard.processor import ImageProcessor

def main():
    try:
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"success": False, "error": "No input provided"}))
            sys.exit(1)
            
        payload = json.loads(input_data)
        
        # Backward compatibility for 'ping' during tests
        if payload.get('action') == 'ping':
            print(json.dumps({"success": True, "data": {"message": "pong"}}))
            sys.exit(0)
            
        if payload.get('action') == 'process':
            processor = ImageProcessor(payload)
            result = processor.execute()
            print(json.dumps(result))
            sys.exit(0)
            
        print(json.dumps({"success": False, "error": f"Unknown action: {payload.get('action')}"}))
        sys.exit(1)
        
    except json.JSONDecodeError as e:
        print(json.dumps({"success": False, "error": f"Invalid JSON input: {str(e)}"}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({"success": False, "error": f"Internal engine error: {str(e)}"}))
        sys.exit(1)

if __name__ == "__main__":
    main()
